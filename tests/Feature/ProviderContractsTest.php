<?php

use App\Domain\Payments\PaymentUnavailable;
use App\Domain\Shipping\Cep;
use App\Domain\Shipping\Data\Shipment;
use App\Domain\Shipping\ShippingUnavailable;
use App\Infrastructure\Payments\PayPalGateway;
use App\Infrastructure\Shipping\MelhorEnvioShippingProvider;
use App\Infrastructure\Shipping\ViaCepAddressLookup;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

/** Responses recorded from the providers' documentation and sandboxes (tests/Fixtures). */
function recorded(string $file): array
{
    return json_decode((string) file_get_contents(base_path("tests/Fixtures/{$file}")), true);
}

function paypal(string $mode = 'sandbox'): PayPalGateway
{
    return new PayPalGateway('client', 'secret', $mode, 'WH-ID', cache()->store());
}

function melhorEnvio(): MelhorEnvioShippingProvider
{
    return new MelhorEnvioShippingProvider('token', 'sandbox', '88501000', ['length' => 16, 'width' => 11, 'height' => 2], ['name' => 'OVNIPORTO'], 'OVNIPORTO (test)');
}

it('talks to the PayPal sandbox when the mode says so, and live otherwise', function () {
    expect(paypal()->baseUrl())->toBe('https://api-m.sandbox.paypal.com')
        ->and(paypal('live')->baseUrl())->toBe('https://api-m.paypal.com');
});

it('creates a PayPal order with the server total in BRL and an idempotency key', function () {
    Http::fake([
        '*/v1/oauth2/token' => Http::response(recorded('paypal/token.json')),
        '*/v2/checkout/orders' => Http::response(recorded('paypal/create-order.json'), 201),
    ]);

    $id = paypal()->createOrder('OVP-2026-000001', 2850, 'Pedido OVP-2026-000001');

    expect($id)->toBe('5O190127TN364715T');
    Http::assertSent(fn (Request $r) => str_ends_with($r->url(), '/v2/checkout/orders')
        && $r['purchase_units'][0]['amount'] === ['currency_code' => 'BRL', 'value' => '28.50']
        && $r->hasHeader('PayPal-Request-Id', 'create-OVP-2026-000001-2850')
        && str_starts_with($r->url(), 'https://api-m.sandbox.paypal.com'));
});

it('reads a completed capture, and an already-captured order without charging again', function () {
    Http::fake([
        '*/v1/oauth2/token' => Http::response(recorded('paypal/token.json')),
        '*/capture' => Http::sequence()
            ->push(recorded('paypal/capture-order.json'), 201)
            ->push(recorded('paypal/already-captured.json'), 422),
        '*/v2/checkout/orders/5O190127TN364715T' => Http::response(recorded('paypal/capture-order.json')),
    ]);

    $first = paypal()->capture('5O190127TN364715T');
    $again = paypal()->capture('5O190127TN364715T');

    expect($first->isCompleted())->toBeTrue()
        ->and($first->amountCents)->toBe(2850)
        ->and($first->captureId)->toBe('3C679366HH908993F')
        ->and($again->isCompleted())->toBeTrue()
        ->and($again->captureId)->toBe('3C679366HH908993F');
});

it('accepts a webhook only when PayPal verifies the signature', function () {
    $body = json_encode(recorded('paypal/webhook-capture-completed.json'));
    Http::fake([
        '*/v1/oauth2/token' => Http::response(recorded('paypal/token.json')),
        '*/verify-webhook-signature' => Http::sequence()
            ->push(recorded('paypal/verify-success.json'))
            ->push(recorded('paypal/verify-failure.json')),
    ]);

    $notice = paypal()->verifyWebhook(['PAYPAL-TRANSMISSION-SIG' => 'sig'], (string) $body);
    $forged = paypal()->verifyWebhook(['PAYPAL-TRANSMISSION-SIG' => 'forged'], (string) $body);

    expect($notice?->isCaptureCompleted())->toBeTrue()
        ->and($notice?->providerOrderId)->toBe('5O190127TN364715T')
        ->and($notice?->amountCents)->toBe(2850)
        ->and($forged)->toBeNull();
});

it('turns PayPal outages into a friendly unavailable error', function () {
    Http::fake(['*/v1/oauth2/token' => Http::response([], 500)]);

    expect(fn () => paypal()->createOrder('OVP-2026-000001', 2850, 'x'))->toThrow(PaymentUnavailable::class);
});

it('quotes Melhor Envio services cheapest first, skipping the ones with errors', function () {
    Http::fake(['*/api/v2/me/shipment/calculate' => Http::response(recorded('melhorenvio/calculate.json'))]);

    $options = melhorEnvio()->quote(Cep::from('01310-100'), 40, 1600);

    expect(array_map(fn ($o) => [$o->service, $o->priceCents, $o->days], $options))->toBe([['PAC', 2190, 8], ['SEDEX', 3540, 3]]);
    Http::assertSent(fn (Request $r) => str_starts_with($r->url(), 'https://sandbox.melhorenvio.com.br')
        && $r['from']['postal_code'] === '88501000'
        && $r['to']['postal_code'] === '01310100'
        && $r['products'][0]['weight'] === 0.04);
});

it('reports Melhor Envio unavailable when it does not answer', function () {
    Http::fake(['*' => Http::response([], 503)]);

    expect(fn () => melhorEnvio()->quote(Cep::from('01310-100'), 40, 1600))->toThrow(ShippingUnavailable::class);
});

it('buys a label and reads tracking and delivery from Melhor Envio', function () {
    Http::fake([
        '*/api/v2/me/cart' => Http::response(recorded('melhorenvio/cart.json'), 201),
        '*/api/v2/me/shipment/checkout' => Http::response(['purchase' => ['status' => 'paid']]),
        '*/api/v2/me/shipment/generate' => Http::response(['9b1e4d5a-7f6c-4e2a-9c41-2f1d1e5b8a77' => ['status' => true]]),
        '*/api/v2/me/shipment/tracking' => Http::sequence()
            ->push(recorded('melhorenvio/tracking-posted.json'))
            ->push(recorded('melhorenvio/tracking-delivered.json')),
    ]);
    $provider = melhorEnvio();

    $label = $provider->createLabel(new Shipment('OVP-2026-000001', '1', 'Ana Serra', 'ana@example.com', '49999990000', '52998224725', [
        'cep' => '88501-000', 'street' => 'Rua Coronel Córdova', 'number' => '100', 'complement' => null,
        'district' => 'Centro', 'city' => 'Lages', 'state' => 'SC',
    ], [['name' => 'Adesivo (Único)', 'quantity' => 2, 'unitPriceCents' => 800, 'weightGrams' => 20]]));

    expect($label['shipmentId'])->toBe('9b1e4d5a-7f6c-4e2a-9c41-2f1d1e5b8a77')
        ->and($label['trackingCode'])->toBe('AA123456789BR')
        ->and($provider->isDelivered('9b1e4d5a-7f6c-4e2a-9c41-2f1d1e5b8a77'))->toBeTrue();
});

it('fills an address from ViaCEP and returns nothing for an unknown CEP', function () {
    Http::fake([
        'viacep.com.br/ws/88501000/*' => Http::response(recorded('viacep/88501000.json')),
        'viacep.com.br/ws/99999999/*' => Http::response(recorded('viacep/not-found.json')),
    ]);
    $lookup = new ViaCepAddressLookup(cache()->store());

    expect($lookup->find(Cep::from('88501-000')))->toBe(['street' => 'Rua Coronel Córdova', 'district' => 'Centro', 'city' => 'Lages', 'state' => 'SC'])
        ->and($lookup->find(Cep::from('99999-999')))->toBeNull();
});

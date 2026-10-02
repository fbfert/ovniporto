<?php

use App\Domain\Payments\Contracts\PaymentGateway;
use App\Domain\Shipping\Contracts\AddressLookup;
use App\Domain\Shipping\Contracts\ShippingProvider;
use App\Mail\OrderMail;
use App\Models\Member;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use Database\Seeders\ProductSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Support\FakeAddressLookup;
use Tests\Support\FakeGateway;
use Tests\Support\FakeShipping;

beforeEach(function () {
    Mail::fake();
    $this->seed(ProductSeeder::class);
    $this->sticker = ProductVariant::query()->where('sku', 'OVP-ADESIVO')->sole();
    $this->gateway = new FakeGateway;
    $this->shipping = new FakeShipping;
    app()->instance(PaymentGateway::class, $this->gateway);
    app()->instance(ShippingProvider::class, $this->shipping);
    app()->instance(AddressLookup::class, new FakeAddressLookup);
});

function buyer(array $overrides = []): array
{
    return [
        'name' => 'Ana Serra',
        'email' => 'ana@example.com',
        'phone' => '(49) 99999-0000',
        'cpf' => '529.982.247-25',
        ...$overrides,
    ];
}

function delivered(array $overrides = []): array
{
    return [
        ...buyer(),
        'pickup' => false,
        'address' => [
            'cep' => '88501-000', 'street' => 'Rua Coronel Córdova', 'number' => '100',
            'complement' => null, 'district' => 'Centro', 'city' => 'Lages', 'state' => 'SC',
        ],
        'shippingOptionId' => '1',
        ...$overrides,
    ];
}

/** Two stickers in the cart, order placed with PAC (R$ 12,50). */
function placeOrder(object $test, array $data = []): Order
{
    $test->post('/carrinho/itens', ['variantId' => $test->sticker->id, 'quantity' => 2]);
    $test->post('/checkout', delivered($data))->assertSessionHasNoErrors();

    return Order::query()->latest('id')->firstOrFail();
}

it('refuses a CPF with a wrong check digit at the first step', function () {
    $this->post('/checkout/identificacao', buyer(['cpf' => '529.982.247-24']))->assertSessionHasErrors('cpf');
    $this->post('/checkout/identificacao', buyer())->assertSessionHasNoErrors();
});

it('fills the address from the CEP and quotes freight on the server', function () {
    $this->post('/carrinho/itens', ['variantId' => $this->sticker->id]);

    $this->postJson('/checkout/entrega', ['cep' => '88501000'])
        ->assertOk()
        ->assertJsonPath('address.city', 'Lages')
        ->assertJsonPath('options.0.priceCents', 1250);
});

it('keeps pickup available when the freight provider is down', function () {
    $this->shipping->down = true;
    $this->post('/carrinho/itens', ['variantId' => $this->sticker->id]);

    $this->postJson('/checkout/entrega', ['cep' => '88501000'])
        ->assertOk()
        ->assertJsonPath('options', [])
        ->assertJsonPath('message', fn (string $m) => str_contains($m, 'retirar em Lages'));

    $this->post('/checkout', [...buyer(), 'pickup' => true])->assertSessionHasNoErrors();
    expect(Order::query()->sole()->shipping_cents)->toBe(0);
});

it('totals items at catalog prices plus the chosen freight, ignoring amounts from the browser', function () {
    $order = placeOrder($this, ['totalCents' => 1, 'shippingPriceCents' => 0]);

    expect($order->subtotal_cents)->toBe(1600)
        ->and($order->shipping_cents)->toBe(1250)
        ->and($order->total_cents)->toBe(2850)
        ->and($order->number)->toMatch('/^OVP-\d{4}-\d{6}$/')
        ->and($order->status->value)->toBe('pending_payment');
    Mail::assertQueued(OrderMail::class, fn ($mail) => $mail->hasTo('ana@example.com') && str_contains($mail->subjectLine, 'recebido'));
});

it('charges zero freight for pickup without asking the provider', function () {
    $this->post('/carrinho/itens', ['variantId' => $this->sticker->id]);
    $this->post('/checkout', [...buyer(), 'pickup' => true])->assertSessionHasNoErrors();

    expect(Order::query()->sole())->shipping_cents->toBe(0)->total_cents->toBe(800)->pickup->toBeTrue()
        ->and($this->shipping->quotes)->toBe(0);
});

it('stores the CPF encrypted', function () {
    $order = placeOrder($this);

    $raw = DB::table('orders')->where('id', $order->id)->value('customer_cpf');
    expect($raw)->not->toContain('52998224725')
        ->and($order->fresh()->customer_cpf)->toBe('52998224725');
});

it('does not touch the stock while the order waits for payment', function () {
    placeOrder($this);

    expect($this->sticker->fresh()->stock_qty)->toBe(500);
});

it('creates the provider order with the server total and pays once, even when captured twice', function () {
    $order = placeOrder($this);

    $this->postJson("/pedido/{$order->number}/pagamento", ['amount' => '0.01'])->assertOk()->assertJsonPath('id', "PAYPAL-{$order->number}");
    expect($this->gateway->orders["PAYPAL-{$order->number}"])->toBe(2850);

    $this->postJson("/pedido/{$order->number}/aprovar")->assertOk()->assertJsonPath('paid', true);
    $this->postJson("/pedido/{$order->number}/aprovar")->assertOk();

    expect($order->fresh()->status->value)->toBe('paid')
        ->and($this->sticker->fresh()->stock_qty)->toBe(498)
        ->and($this->gateway->captures)->toBe(1)
        ->and($order->events()->where('to_status', 'paid')->count())->toBe(1);
    Mail::assertQueued(OrderMail::class, fn ($mail) => str_contains($mail->subjectLine, 'Pagamento confirmado'));
    $this->get('/loja')->assertInertia(fn (Assert $page) => $page->where('cart.count', 0));
});

it('converges the webhook and the browser return on a single payment', function () {
    $order = placeOrder($this);
    $this->postJson("/pedido/{$order->number}/pagamento");
    $event = json_encode([
        'event_type' => 'PAYMENT.CAPTURE.COMPLETED',
        'resource' => ['id' => 'CAP-1', 'amount' => ['value' => '28.50'], 'supplementary_data' => ['related_ids' => ['order_id' => "PAYPAL-{$order->number}"]]],
    ]);

    $this->call('POST', '/webhooks/paypal', [], [], [], ['HTTP_PAYPAL_TRANSMISSION_SIG' => 'good-signature', 'CONTENT_TYPE' => 'application/json'], $event)->assertOk();
    $this->postJson("/pedido/{$order->number}/aprovar")->assertOk();

    expect($order->fresh()->status->value)->toBe('paid')
        ->and($this->sticker->fresh()->stock_qty)->toBe(498)
        ->and($this->gateway->captures)->toBe(0);
});

it('rejects a webhook with an invalid signature and leaves the order pending', function () {
    $order = placeOrder($this);
    $this->postJson("/pedido/{$order->number}/pagamento");
    $event = json_encode([
        'event_type' => 'PAYMENT.CAPTURE.COMPLETED',
        'resource' => ['id' => 'CAP-1', 'amount' => ['value' => '28.50'], 'supplementary_data' => ['related_ids' => ['order_id' => "PAYPAL-{$order->number}"]]],
    ]);

    $this->call('POST', '/webhooks/paypal', [], [], [], ['HTTP_PAYPAL_TRANSMISSION_SIG' => 'forged', 'CONTENT_TYPE' => 'application/json'], $event)->assertStatus(400);

    expect($order->fresh()->status->value)->toBe('pending_payment');
});

it('lets a member complete the checkout and see the order in the account', function () {
    $member = Member::factory()->create();
    $this->actingAs($member);
    $order = placeOrder($this);

    expect($order->member_id)->toBe($member->id);
    $this->get("/pedido/{$order->number}")->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('Orders/Show')
        ->where('order.customer.cpf', '***.982.247-**')
    );
});

it('keeps the paid price when the product price changes later', function () {
    $order = placeOrder($this);
    Product::query()->where('slug', 'adesivo-ovniporto')->update(['price_cents' => 1500]);

    expect($order->items()->sole()->unit_price_cents)->toBe(800)
        ->and($order->fresh()->total_cents)->toBe(2850);
});

it('opens the order page only with the signed link or as its owner', function () {
    $order = placeOrder($this);
    $this->flushSession();

    $this->get("/pedido/{$order->number}")->assertForbidden();
    $this->get(URL::signedRoute('order.show', ['number' => $order->number]))->assertOk();
    $this->actingAs(Member::factory()->create())->get("/pedido/{$order->number}")->assertForbidden();
});

it('cancels orders still unpaid after 2 hours, by the system', function () {
    $order = placeOrder($this);
    $this->travel(3)->hours();

    $this->artisan('orders:cancel-abandoned')->assertSuccessful();

    expect($order->fresh()->status->value)->toBe('canceled')
        ->and($order->events()->reorder('id', 'desc')->first())->actor->toBe('system')->to_status->toBe('canceled');
});

it('refuses checkout to a blocked member', function () {
    $member = Member::factory()->create(['blocked_at' => now(), 'blocked_reason' => 'Motivo registrado no painel.']);
    $this->actingAs($member);
    $this->post('/carrinho/itens', ['variantId' => $this->sticker->id]);

    $this->post('/checkout', delivered())->assertSessionHasErrors('checkout');
});

it('anonymizes the orders when the member deletes the account', function () {
    $member = Member::factory()->create(['nickname' => 'coruja']);
    $this->actingAs($member);
    $order = placeOrder($this);

    $this->delete('/conta', ['confirmation' => 'coruja']);

    expect($order->fresh())
        ->member_id->toBeNull()
        ->customer_name->toBeNull()
        ->customer_email->toBeNull()
        ->total_cents->toBe(2850)
        ->and($order->fresh()->address)->toBe(['city' => 'Lages', 'state' => 'SC']);
});

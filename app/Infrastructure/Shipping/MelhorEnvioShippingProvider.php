<?php

namespace App\Infrastructure\Shipping;

use App\Domain\Catalog\Money;
use App\Domain\Shipping\Cep;
use App\Domain\Shipping\Contracts\ShippingProvider;
use App\Domain\Shipping\Data\Shipment;
use App\Domain\Shipping\Data\ShippingOption;
use App\Domain\Shipping\ShippingUnavailable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

/**
 * Melhor Envio API v2 (sandbox or production by MELHOR_ENVIO_MODE). Quotes by
 * weight and one standard package; labels are bought with the account balance.
 * Only what delivery needs is sent: name, contact, CPF and address.
 */
final readonly class MelhorEnvioShippingProvider implements ShippingProvider
{
    /**
     * @param  array{length: int, width: int, height: int}  $package  cm
     * @param  array<string, string>  $sender  name, phone, email, document, street, number, district, city, state, cep
     */
    public function __construct(
        private string $token,
        private string $mode,
        private string $fromCep,
        private array $package,
        private array $sender,
        private string $userAgent,
    ) {}

    public function baseUrl(): string
    {
        return $this->mode === 'production' ? 'https://melhorenvio.com.br' : 'https://sandbox.melhorenvio.com.br';
    }

    public function quote(Cep $to, int $weightGrams, int $valueCents): array
    {
        $response = $this->send(fn (PendingRequest $http) => $http->post('/api/v2/me/shipment/calculate', [
            'from' => ['postal_code' => $this->fromCep],
            'to' => ['postal_code' => $to->digits],
            'products' => [[
                'id' => 'pedido',
                ...$this->package,
                'weight' => max(0.01, $weightGrams / 1000),
                'insurance_value' => Money::cents($valueCents)->decimal(),
                'quantity' => 1,
            ]],
            'options' => ['receipt' => false, 'own_hand' => false],
        ]));

        $options = [];
        foreach ((array) $response->json() as $service) {
            if (! is_array($service) || isset($service['error']) || ! isset($service['price'])) {
                continue;
            }
            $options[] = new ShippingOption(
                id: (string) $service['id'],
                carrier: (string) ($service['company']['name'] ?? ''),
                service: (string) ($service['name'] ?? ''),
                priceCents: self::cents((string) ($service['custom_price'] ?? $service['price'])),
                days: (int) ($service['custom_delivery_time'] ?? $service['delivery_time'] ?? 0),
            );
        }
        usort($options, fn (ShippingOption $a, ShippingOption $b) => $a->priceCents <=> $b->priceCents);

        return $options;
    }

    public function createLabel(Shipment $shipment): array
    {
        $insurance = array_sum(array_map(fn (array $i) => $i['unitPriceCents'] * $i['quantity'], $shipment->items));
        $weight = array_sum(array_map(fn (array $i) => $i['weightGrams'] * $i['quantity'], $shipment->items));
        $a = $shipment->address;

        $cart = $this->send(fn (PendingRequest $http) => $http->post('/api/v2/me/cart', [
            'service' => (int) $shipment->serviceId,
            'from' => [
                'name' => $this->sender['name'] ?? '', 'phone' => $this->sender['phone'] ?? '', 'email' => $this->sender['email'] ?? '',
                'document' => $this->sender['document'] ?? '', 'address' => $this->sender['street'] ?? '', 'number' => $this->sender['number'] ?? '',
                'district' => $this->sender['district'] ?? '', 'city' => $this->sender['city'] ?? '', 'state_abbr' => $this->sender['state'] ?? '',
                'postal_code' => $this->fromCep, 'country_id' => 'BR',
            ],
            'to' => [
                'name' => $shipment->recipientName, 'phone' => $shipment->recipientPhone, 'email' => $shipment->recipientEmail,
                'document' => $shipment->recipientCpf, 'address' => $a['street'], 'number' => $a['number'], 'complement' => $a['complement'] ?? '',
                'district' => $a['district'], 'city' => $a['city'], 'state_abbr' => $a['state'], 'postal_code' => $a['cep'], 'country_id' => 'BR',
            ],
            'products' => array_map(fn (array $i) => [
                'name' => $i['name'], 'quantity' => $i['quantity'], 'unitary_value' => Money::cents($i['unitPriceCents'])->decimal(),
            ], $shipment->items),
            'volumes' => [[...$this->package, 'weight' => max(0.01, $weight / 1000)]],
            'options' => [
                'insurance_value' => Money::cents($insurance)->decimal(),
                'receipt' => false, 'own_hand' => false, 'reverse' => false, 'non_commercial' => true,
                'tags' => [['tag' => $shipment->orderNumber]],
            ],
        ]));
        $id = (string) $cart->json('id');

        $this->send(fn (PendingRequest $http) => $http->post('/api/v2/me/shipment/checkout', ['orders' => [$id]]));
        $this->send(fn (PendingRequest $http) => $http->post('/api/v2/me/shipment/generate', ['orders' => [$id]]));

        return ['shipmentId' => $id, 'trackingCode' => $this->trackingCode($id), 'trackingUrl' => null];
    }

    public function isDelivered(string $shipmentId): bool
    {
        $response = $this->send(fn (PendingRequest $http) => $http->post('/api/v2/me/shipment/tracking', ['orders' => [$shipmentId]]));

        return $response->json("{$shipmentId}.status") === 'delivered';
    }

    public function isSimulated(): bool
    {
        return false;
    }

    private function trackingCode(string $shipmentId): ?string
    {
        try {
            $code = $this->send(fn (PendingRequest $http) => $http->post('/api/v2/me/shipment/tracking', ['orders' => [$shipmentId]]))
                ->json("{$shipmentId}.tracking");
        } catch (ShippingUnavailable) {
            return null;
        }

        return is_string($code) && $code !== '' ? $code : null;
    }

    /** @param callable(PendingRequest): Response $call */
    private function send(callable $call): Response
    {
        try {
            $response = $call(Http::baseUrl($this->baseUrl())
                ->withToken($this->token)
                ->withUserAgent($this->userAgent)
                ->acceptJson()
                ->asJson()
                ->timeout(15));
        } catch (ConnectionException) {
            throw new ShippingUnavailable;
        }
        if (! $response->successful()) {
            report(new \RuntimeException("Melhor Envio answered {$response->status()}: {$response->body()}"));
            throw new ShippingUnavailable;
        }

        return $response;
    }

    /** "21.90" → 2190. */
    private static function cents(string $value): int
    {
        [$reais, $centavos] = array_pad(explode('.', $value, 2), 2, '0');

        return (int) $reais * 100 + (int) str_pad(substr($centavos, 0, 2), 2, '0');
    }
}

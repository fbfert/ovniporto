<?php

namespace App\Infrastructure\Payments;

use App\Domain\Catalog\Money;
use App\Domain\Payments\Contracts\PaymentGateway;
use App\Domain\Payments\Data\CaptureResult;
use App\Domain\Payments\Data\WebhookNotice;
use App\Domain\Payments\PaymentUnavailable;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

/**
 * PayPal Orders v2. Sandbox or live comes from PAYPAL_MODE; credentials only
 * from the environment. The buyer pays inside PayPal's own components (card
 * fields included), so card data never reaches this server.
 */
final readonly class PayPalGateway implements PaymentGateway
{
    public function __construct(
        private string $clientId,
        private string $secret,
        private string $mode,
        private ?string $webhookId,
        private Cache $cache,
    ) {}

    public function baseUrl(): string
    {
        return $this->mode === 'live' ? 'https://api-m.paypal.com' : 'https://api-m.sandbox.paypal.com';
    }

    public function createOrder(string $orderNumber, int $totalCents, string $description): string
    {
        $response = $this->send(fn (PendingRequest $http) => $http
            ->withHeaders(['PayPal-Request-Id' => "create-{$orderNumber}-{$totalCents}"])
            ->post('/v2/checkout/orders', [
                'intent' => 'CAPTURE',
                'purchase_units' => [[
                    'reference_id' => $orderNumber,
                    'invoice_id' => $orderNumber,
                    'description' => mb_substr($description, 0, 127),
                    'amount' => ['currency_code' => 'BRL', 'value' => Money::cents($totalCents)->decimal()],
                ]],
                'application_context' => ['brand_name' => 'OVNIPORTO', 'shipping_preference' => 'NO_SHIPPING'],
            ]));

        return (string) $response->json('id');
    }

    public function capture(string $providerOrderId): CaptureResult
    {
        $response = $this->send(fn (PendingRequest $http) => $http
            ->withHeaders(['PayPal-Request-Id' => "capture-{$providerOrderId}", 'Prefer' => 'return=representation'])
            ->post("/v2/checkout/orders/{$providerOrderId}/capture"), allowAlreadyCaptured: true);

        if ($response->status() === 422) {
            // Already captured (a retry, or the webhook won the race): read the order as it is.
            $response = $this->send(fn (PendingRequest $http) => $http->get("/v2/checkout/orders/{$providerOrderId}"));
        }

        /** @var array{id?: string, status?: string, amount?: array{value?: string}} $capture */
        $capture = (array) $response->json('purchase_units.0.payments.captures.0', []);

        return new CaptureResult(
            status: match ($capture['status'] ?? null) {
                'COMPLETED' => CaptureResult::COMPLETED,
                'PENDING' => CaptureResult::PENDING,
                default => CaptureResult::DECLINED,
            },
            captureId: $capture['id'] ?? null,
            amountCents: self::cents($capture['amount']['value'] ?? '0'),
        );
    }

    public function verifyWebhook(array $headers, string $body): ?WebhookNotice
    {
        $event = json_decode($body, true);
        if (! is_array($event) || $this->webhookId === null || $this->webhookId === '') {
            return null;
        }
        $headers = array_change_key_case($headers, CASE_LOWER);

        try {
            $response = $this->send(fn (PendingRequest $http) => $http->post('/v1/notifications/verify-webhook-signature', [
                'auth_algo' => $headers['paypal-auth-algo'] ?? '',
                'cert_url' => $headers['paypal-cert-url'] ?? '',
                'transmission_id' => $headers['paypal-transmission-id'] ?? '',
                'transmission_sig' => $headers['paypal-transmission-sig'] ?? '',
                'transmission_time' => $headers['paypal-transmission-time'] ?? '',
                'webhook_id' => $this->webhookId,
                'webhook_event' => $event,
            ]));
        } catch (PaymentUnavailable) {
            return null;
        }
        if ($response->json('verification_status') !== 'SUCCESS') {
            return null;
        }

        return new WebhookNotice(
            event: (string) ($event['event_type'] ?? ''),
            providerOrderId: $event['resource']['supplementary_data']['related_ids']['order_id'] ?? null,
            captureId: $event['resource']['id'] ?? null,
            amountCents: self::cents((string) ($event['resource']['amount']['value'] ?? '0')),
        );
    }

    public function refund(string $captureId, int $amountCents, string $orderNumber): void
    {
        $this->send(fn (PendingRequest $http) => $http
            ->withHeaders(['PayPal-Request-Id' => "refund-{$captureId}"])
            ->post("/v2/payments/captures/{$captureId}/refund", [
                'amount' => ['currency_code' => 'BRL', 'value' => Money::cents($amountCents)->decimal()],
                'invoice_id' => "{$orderNumber}-refund",
                'note_to_payer' => "Reembolso do pedido {$orderNumber}",
            ]));
    }

    public function isSimulated(): bool
    {
        return false;
    }

    /** @param callable(PendingRequest): Response $call */
    private function send(callable $call, bool $allowAlreadyCaptured = false): Response
    {
        try {
            $response = $call(Http::baseUrl($this->baseUrl())->withToken($this->token())->acceptJson()->asJson()->timeout(20));
        } catch (ConnectionException) {
            throw new PaymentUnavailable;
        }
        if ($response->successful() || ($allowAlreadyCaptured && $response->status() === 422 && str_contains($response->body(), 'ORDER_ALREADY_CAPTURED'))) {
            return $response;
        }

        report(new \RuntimeException("PayPal answered {$response->status()}: {$response->body()}"));
        throw new PaymentUnavailable;
    }

    private function token(): string
    {
        return (string) $this->cache->remember("paypal:token:{$this->mode}", 3000, function () {
            try {
                $response = Http::baseUrl($this->baseUrl())
                    ->withBasicAuth($this->clientId, $this->secret)
                    ->asForm()
                    ->timeout(15)
                    ->post('/v1/oauth2/token', ['grant_type' => 'client_credentials']);
            } catch (ConnectionException) {
                throw new PaymentUnavailable;
            }
            if (! $response->successful()) {
                throw new PaymentUnavailable('As credenciais do PayPal foram recusadas.');
            }

            return (string) $response->json('access_token');
        });
    }

    /** "28.50" → 2850, without floats. */
    private static function cents(string $value): int
    {
        [$reais, $centavos] = array_pad(explode('.', $value, 2), 2, '0');

        return (int) $reais * 100 + (int) str_pad(substr($centavos, 0, 2), 2, '0');
    }
}

<?php

namespace App\Infrastructure\Payments;

use App\Domain\Payments\Contracts\PaymentGateway;
use App\Domain\Payments\Data\CaptureResult;
use App\Domain\Payments\Data\WebhookNotice;
use Illuminate\Contracts\Cache\Repository as Cache;

/**
 * Local development only (never bound in production): lets the whole checkout
 * run before the PayPal sandbox credentials exist. The page shows a
 * "simulate approval" button instead of the provider's buttons.
 */
final readonly class SimulatedPaymentGateway implements PaymentGateway
{
    public function __construct(private Cache $cache) {}

    public function createOrder(string $orderNumber, int $totalCents, string $description): string
    {
        $id = "SIM-{$orderNumber}";
        $this->cache->put("simulated-payment:{$id}", $totalCents, 86_400);

        return $id;
    }

    public function capture(string $providerOrderId): CaptureResult
    {
        $amount = $this->cache->get("simulated-payment:{$providerOrderId}");

        return is_int($amount)
            ? new CaptureResult(CaptureResult::COMPLETED, "CAP-{$providerOrderId}", $amount)
            : new CaptureResult(CaptureResult::DECLINED, null, 0);
    }

    public function verifyWebhook(array $headers, string $body): ?WebhookNotice
    {
        return null;
    }

    public function isSimulated(): bool
    {
        return true;
    }
}

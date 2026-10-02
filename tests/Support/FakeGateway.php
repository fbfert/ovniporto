<?php

namespace Tests\Support;

use App\Domain\Payments\Contracts\PaymentGateway;
use App\Domain\Payments\Data\CaptureResult;
use App\Domain\Payments\Data\WebhookNotice;

final class FakeGateway implements PaymentGateway
{
    /** @var array<string, int> */
    public array $orders = [];

    public int $captures = 0;

    public string $validSignature = 'good-signature';

    public function createOrder(string $orderNumber, int $totalCents, string $description): string
    {
        $id = 'PAYPAL-'.$orderNumber;
        $this->orders[$id] = $totalCents;

        return $id;
    }

    public function capture(string $providerOrderId): CaptureResult
    {
        $this->captures++;

        return new CaptureResult(CaptureResult::COMPLETED, 'CAPTURE-'.$providerOrderId, $this->orders[$providerOrderId] ?? 0);
    }

    public function verifyWebhook(array $headers, string $body): ?WebhookNotice
    {
        if (($headers['paypal-transmission-sig'] ?? '') !== $this->validSignature) {
            return null;
        }
        $event = (array) json_decode($body, true);

        return new WebhookNotice(
            (string) ($event['event_type'] ?? ''),
            $event['resource']['supplementary_data']['related_ids']['order_id'] ?? null,
            $event['resource']['id'] ?? null,
            (int) round(((float) ($event['resource']['amount']['value'] ?? 0)) * 100),
        );
    }

    public function isSimulated(): bool
    {
        return false;
    }
}

<?php

namespace App\Domain\Payments\Data;

/** A verified provider notification we care about: a capture finished for one of our orders. */
final readonly class WebhookNotice
{
    public function __construct(
        public string $event,
        public ?string $providerOrderId,
        public ?string $captureId,
        public int $amountCents,
    ) {}

    public function isCaptureCompleted(): bool
    {
        return $this->event === 'PAYMENT.CAPTURE.COMPLETED';
    }
}

<?php

namespace App\Domain\Payments\Data;

/** What the provider says after a capture: completed (money in), pending (e.g. under review) or declined. */
final readonly class CaptureResult
{
    public const COMPLETED = 'completed';

    public const PENDING = 'pending';

    public const DECLINED = 'declined';

    public function __construct(
        public string $status,
        public ?string $captureId,
        public int $amountCents,
    ) {}

    public function isCompleted(): bool
    {
        return $this->status === self::COMPLETED;
    }
}

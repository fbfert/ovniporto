<?php

namespace App\Domain\Campaign\Data;

use DateTimeImmutable;

/** A supporter as the admin types it or as a CSV line brings it. */
final readonly class SupporterEntry
{
    public function __construct(
        public string $name,
        public ?int $amountCents = null,
        public ?string $reward = null,
        public bool $publishName = false,
        public ?DateTimeImmutable $supportedAt = null,
    ) {}
}

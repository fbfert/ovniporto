<?php

namespace App\Domain\Campaign\Data;

use App\Domain\Campaign\CampaignStatus;

final readonly class CampaignSettings
{
    public function __construct(
        public CampaignStatus $status,
        public ?int $goalCents = null,
        public ?int $raisedCents = null,
        public ?string $crowdfundingUrl = null,
        public ?float $storeSharePercent = null,
    ) {}

    /** What a fresh install starts with: planning, everything else empty. */
    public static function initial(): self
    {
        return new self(CampaignStatus::Planning);
    }
}

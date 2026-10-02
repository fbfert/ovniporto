<?php

namespace App\Domain\Sightings\Data;

use App\Domain\Sightings\SightingStatus;

/** The outcome of a moderation step: the new status, what the author reads, and whether it goes public. */
final readonly class ModerationDecision
{
    public function __construct(
        public SightingStatus $status,
        public ?string $noteToAuthor,
        public bool $publish,
    ) {}
}

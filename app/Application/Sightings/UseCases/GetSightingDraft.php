<?php

namespace App\Application\Sightings\UseCases;

use App\Domain\Sightings\Contracts\MemberSightingRepository;
use App\Domain\Sightings\ModerationRules;
use App\Domain\Sightings\SightingStatus;

/** Loads a report the tower sent back, so the author can fix it in the same wizard. */
final readonly class GetSightingDraft
{
    public function __construct(private MemberSightingRepository $sightings) {}

    /** @return array<string, mixed>|null null unless the report is the member's and waits for an adjustment */
    public function execute(int $memberId, int $sightingId): ?array
    {
        $draft = $this->sightings->draftOf($memberId, $sightingId);
        if ($draft === null || ! ModerationRules::canResubmit(SightingStatus::from($draft['status']))) {
            return null;
        }

        return $draft;
    }
}

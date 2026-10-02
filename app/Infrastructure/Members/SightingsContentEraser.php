<?php

namespace App\Infrastructure\Members;

use App\Domain\Members\Contracts\MemberContentEraser;
use App\Domain\Sightings\Contracts\MemberSightingRepository;

/** Account deletion, Sightings side: every report and photo of the member goes. */
final readonly class SightingsContentEraser implements MemberContentEraser
{
    public function __construct(private MemberSightingRepository $sightings) {}

    public function eraseFor(int $memberId): void
    {
        $this->sightings->deleteAllOf($memberId);
    }
}

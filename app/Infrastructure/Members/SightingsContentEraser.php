<?php

namespace App\Infrastructure\Members;

use App\Domain\Members\Contracts\MemberContentEraser;
use App\Domain\Sightings\Contracts\MemberSightingRepository;

/** Account deletion, Sightings side: every report, photo, variant and unclaimed upload of the member goes. */
final readonly class SightingsContentEraser implements MemberContentEraser
{
    public function __construct(private MemberSightingRepository $sightings) {}

    public function eraseFor(int $memberId, string $email): void
    {
        $this->sightings->deleteAllOf($memberId);
    }
}

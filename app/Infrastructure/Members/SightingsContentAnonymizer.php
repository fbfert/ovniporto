<?php

namespace App\Infrastructure\Members;

use App\Domain\Members\Contracts\MemberContentAnonymizer;
use App\Domain\Sightings\Contracts\MemberSightingRepository;

/**
 * Account anonymized after inactivity, Sightings side: published reports stay in the Livro and on
 * the map with the nickname; reports never published, their photos and unclaimed uploads go.
 */
final readonly class SightingsContentAnonymizer implements MemberContentAnonymizer
{
    public function __construct(private MemberSightingRepository $sightings) {}

    public function anonymizeFor(int $memberId, string $email): void
    {
        $this->sightings->deleteUnpublishedOf($memberId);
    }
}

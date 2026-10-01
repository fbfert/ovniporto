<?php

namespace App\Domain\Sightings\Contracts;

use App\Domain\Sightings\Data\SightingCard;

/**
 * Public read side of the Livro de avistamentos. Implementations MUST only
 * expose approved and published sightings.
 */
interface SightingReadRepository
{
    public function countApproved(): int;

    /** @return list<SightingCard> */
    public function latestApproved(int $limit): array;
}

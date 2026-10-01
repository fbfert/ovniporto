<?php

namespace App\Domain\Place\Contracts;

use App\Domain\Place\Data\PlaceSpaceCard;

interface PlaceSpaceRepository
{
    /** @return list<PlaceSpaceCard> Ordered by phase, then sort order. */
    public function all(): array;
}

<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Place\Contracts\PlaceSpaceRepository;
use App\Domain\Place\Data\PlaceSpaceCard;
use App\Models\PlaceSpace;

final class EloquentPlaceSpaceRepository implements PlaceSpaceRepository
{
    public function all(): array
    {
        return PlaceSpace::query()
            ->orderBy('phase')
            ->orderBy('sort_order')
            ->get()
            ->map(fn (PlaceSpace $space) => new PlaceSpaceCard(
                slug: $space->slug,
                name: $space->name,
                role: $space->role,
                phase: $space->phase,
                status: $space->status,
            ))
            ->values()
            ->all();
    }
}

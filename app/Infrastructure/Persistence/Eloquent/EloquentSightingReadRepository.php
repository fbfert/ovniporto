<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Sightings\Contracts\SightingReadRepository;
use App\Domain\Sightings\Data\SightingCard;
use App\Models\Sighting;
use DateTimeImmutable;
use Illuminate\Support\Facades\Storage;

final class EloquentSightingReadRepository implements SightingReadRepository
{
    public function countApproved(): int
    {
        return Sighting::query()->approved()->count();
    }

    public function latestApproved(int $limit): array
    {
        return Sighting::query()
            ->approved()
            ->with('photos')
            ->latest('published_at')
            ->limit($limit)
            ->get()
            ->map(fn (Sighting $sighting) => new SightingCard(
                id: $sighting->id,
                type: $sighting->type,
                placeLabel: $sighting->place_label,
                observedDate: DateTimeImmutable::createFromInterface($sighting->observed_date),
                nickname: $sighting->public_nickname,
                photoUrl: ($photo = $sighting->photos->first())
                    ? Storage::disk('public')->url($photo->path)
                    : null,
            ))
            ->values()
            ->all();
    }
}

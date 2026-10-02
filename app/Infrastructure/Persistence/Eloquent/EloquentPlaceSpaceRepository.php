<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Place\Contracts\PlaceSpaceRepository;
use App\Domain\Place\Data\PlaceSpaceCard;
use App\Models\PlaceSpace;
use Illuminate\Support\Facades\Storage;

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
                concept: $space->concept_image_path,
                description: $space->description,
                conceptUrl: $this->uploadedUrl($space->concept_image_path),
            ))
            ->values()
            ->all();
    }

    /** Bundled concepts are manifest slugs ("hangar"); panel uploads are files ("concepts/….webp"). */
    private function uploadedUrl(?string $concept): ?string
    {
        return $concept !== null && str_contains($concept, '/') ? Storage::disk('public')->url($concept) : null;
    }
}

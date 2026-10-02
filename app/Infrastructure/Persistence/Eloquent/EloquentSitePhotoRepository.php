<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Place\Contracts\SitePhotoRepository;
use App\Models\SitePhoto;
use Illuminate\Support\Facades\Storage;

final class EloquentSitePhotoRepository implements SitePhotoRepository
{
    public function all(): array
    {
        return SitePhoto::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (SitePhoto $photo) => [
                'url' => Storage::disk('public')->url((string) $photo->path),
                'alt' => (string) $photo->alt,
                'caption' => $photo->caption,
                'takenAt' => $photo->taken_at?->toDateString(),
            ])
            ->values()
            ->all();
    }
}

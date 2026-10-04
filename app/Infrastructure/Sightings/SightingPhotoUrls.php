<?php

namespace App\Infrastructure\Sightings;

use App\Models\SightingPhoto;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

/**
 * Where a report photo can be fetched. Processed photos go through the photo
 * route (public when approved, signed otherwise); the demo photos generated
 * before that pipeline still live on the public disk.
 */
final class SightingPhotoUrls
{
    public const SIGNED_MINUTES = 10;

    public static function public(SightingPhoto $photo, int $width = 800): ?string
    {
        if ($photo->variants === null) {
            return $photo->processed_at === null && str_starts_with($photo->path, 'demo/')
                ? Storage::disk('public')->url($photo->path)
                : null;
        }

        return route('sighting.photo', ['photo' => $photo->id, 'width' => self::closest($photo->variants, $width)]);
    }

    public static function signed(SightingPhoto $photo, int $width = 800): ?string
    {
        if ($photo->variants === null) {
            return self::public($photo, $width);
        }

        return URL::temporarySignedRoute(
            'sighting.photo',
            now()->addMinutes(self::SIGNED_MINUTES),
            ['photo' => $photo->id, 'width' => self::closest($photo->variants, $width)],
        );
    }

    /** @param list<int> $variants */
    public static function closest(array $variants, int $width): int
    {
        $larger = array_filter($variants, fn (int $w) => $w >= $width);

        return $larger === [] ? max($variants) : min($larger);
    }
}

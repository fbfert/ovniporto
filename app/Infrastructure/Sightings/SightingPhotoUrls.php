<?php

namespace App\Infrastructure\Sightings;

use App\Domain\Sightings\SightingPhotoFiles;
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

    /**
     * What <Picture> needs: WebP and AVIF srcsets plus the 20 px placeholder, public or
     * signed like the photo itself. Null for photos without variants (seeded demo files).
     *
     * @return array{webp: string, avif: ?string, placeholder: ?string, width: int, height: int}|null
     */
    public static function sources(SightingPhoto $photo, bool $signed = false): ?array
    {
        $widths = $photo->variants ?? [];
        if ($widths === []) {
            return null;
        }
        $url = fn (int $width, string $format) => self::url($photo, $width, $format, $signed);
        $srcset = fn (string $format) => implode(', ', array_map(fn (int $w) => $url($w, $format)." {$w}w", $widths));

        return [
            'webp' => $srcset('webp'),
            'avif' => $photo->avif ? $srcset('avif') : null,
            'placeholder' => $photo->avif ? $url(SightingPhotoFiles::PLACEHOLDER_WIDTH, 'webp') : null,
            'width' => $photo->width,
            'height' => $photo->height,
        ];
    }

    private static function url(SightingPhoto $photo, int $width, string $format, bool $signed): string
    {
        $parameters = ['photo' => $photo->id, 'width' => $width, 'format' => $format === 'webp' ? null : $format];

        return $signed
            ? URL::temporarySignedRoute('sighting.photo', now()->addMinutes(self::SIGNED_MINUTES), $parameters)
            : route('sighting.photo', $parameters);
    }

    /** @param list<int> $variants */
    public static function closest(array $variants, int $width): int
    {
        $larger = array_filter($variants, fn (int $w) => $w >= $width);

        return $larger === [] ? max($variants) : min($larger);
    }
}

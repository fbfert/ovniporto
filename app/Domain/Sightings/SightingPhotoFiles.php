<?php

namespace App\Domain\Sightings;

/** Every file a processed report photo leaves on disk, so deleting it never forgets one. */
final class SightingPhotoFiles
{
    public const PLACEHOLDER_WIDTH = 20;

    /**
     * @param  string  $path  the photo's path: the original upload, or the base the variants hang from
     * @param  list<int>  $widths
     * @return list<string>
     */
    public static function all(string $path, array $widths): array
    {
        $files = [$path];
        foreach ([...$widths, self::PLACEHOLDER_WIDTH] as $width) {
            $files[] = "{$path}-{$width}.webp";
            $files[] = "{$path}-{$width}.avif";
        }

        return $files;
    }
}

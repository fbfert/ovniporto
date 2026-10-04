<?php

namespace App\Infrastructure\Images;

use App\Domain\Content\Contracts\ImageLibrary;
use App\Domain\Sightings\Contracts\ImageProcessor;
use App\Domain\Sightings\Data\ProcessedImage;
use App\Domain\Sightings\UnsupportedImage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/** Panel images on the public disk: AVIF + WebP in four widths and a placeholder, rebuilt from pixels (no metadata). */
final readonly class PublicImageLibrary implements ImageLibrary
{
    public const MAX_WIDTH = 1600;

    /** Same list as RESPONSIVE_WIDTHS in resources/js/Components/Ui/Picture.tsx, which builds the srcset from it. */
    public const WIDTHS = [400, 800, 1200, 1600];

    public function __construct(private ImageProcessor $processor) {}

    /**
     * Writes "{uuid}.webp" (1600 px, the path kept in the database) and its siblings
     * "{uuid}-{w}.avif|webp" for every width plus "{uuid}-20.webp" (placeholder).
     * A smaller original fills the larger widths at its own size, so every sibling exists.
     */
    public function store(string $contents, string $folder): string
    {
        $base = trim($folder, '/').'/'.Str::uuid();
        $this->writeVariants($base, $this->processor->process($contents, self::WIDTHS));

        return "{$base}.webp";
    }

    /** Siblings for an image stored before they existed; false when the file can't be read. */
    public function backfill(string $path): bool
    {
        $disk = Storage::disk('public');
        if (! $disk->exists($path)) {
            return false;
        }

        try {
            $this->writeVariants(substr($path, 0, -5), $this->processor->process((string) $disk->get($path), self::WIDTHS));
        } catch (UnsupportedImage) {
            return false;
        }

        return true;
    }

    public static function hasVariants(string $path): bool
    {
        return Storage::disk('public')->exists(substr($path, 0, -5).'-400.webp');
    }

    private function writeVariants(string $base, ProcessedImage $image): void
    {
        $disk = Storage::disk('public');
        $largest = max(array_keys($image->variants));
        foreach (self::WIDTHS as $width) {
            $available = $this->closestBelow(array_keys($image->variants), $width);
            $disk->put("{$base}-{$width}.webp", $image->variants[$available]);
            if (isset($image->avif[$available])) {
                $disk->put("{$base}-{$width}.avif", $image->avif[$available]);
            }
        }
        $disk->put("{$base}-20.webp", $image->placeholder);
        if (! $disk->exists("{$base}.webp")) {
            $disk->put("{$base}.webp", $image->variants[$largest]);
        }
    }

    /** @param list<int> $available */
    private function closestBelow(array $available, int $width): int
    {
        $fitting = array_filter($available, fn (int $w) => $w <= $width);

        return $fitting === [] ? min($available) : max($fitting);
    }

    public function storeSquare(string $contents, string $folder): string
    {
        $image = @imagecreatefromstring($contents);
        if ($image === false) {
            throw new UnsupportedImage('The image could not be decoded.');
        }
        $side = min(imagesx($image), imagesy($image));
        $square = imagecrop($image, [
            'x' => intdiv(imagesx($image) - $side, 2),
            'y' => intdiv(imagesy($image) - $side, 2),
            'width' => $side,
            'height' => $side,
        ]);
        if ($square === false) {
            throw new UnsupportedImage('The image could not be cropped.');
        }
        ob_start();
        imagepng($square);

        return $this->store((string) ob_get_clean(), $folder);
    }

    public function delete(?string $path): void
    {
        // Seeded demo files and manifest concepts are not ours to erase.
        if ($path !== null && str_ends_with($path, '.webp') && ! str_starts_with($path, 'demo/')) {
            $base = substr($path, 0, -5);
            Storage::disk('public')->delete([
                $path,
                "{$base}-20.webp",
                ...array_merge(...array_map(fn (int $w) => ["{$base}-{$w}.webp", "{$base}-{$w}.avif"], self::WIDTHS)),
            ]);
        }
    }
}

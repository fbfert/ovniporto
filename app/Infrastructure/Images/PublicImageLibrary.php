<?php

namespace App\Infrastructure\Images;

use App\Domain\Content\Contracts\ImageLibrary;
use App\Domain\Sightings\Contracts\ImageProcessor;
use App\Domain\Sightings\UnsupportedImage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/** One WebP up to 1600 px wide on the public disk, rebuilt from pixels (no metadata). */
final readonly class PublicImageLibrary implements ImageLibrary
{
    public const MAX_WIDTH = 1600;

    public function __construct(private ImageProcessor $processor) {}

    public function store(string $contents, string $folder): string
    {
        $image = $this->processor->process($contents, [self::MAX_WIDTH]);
        $path = trim($folder, '/').'/'.Str::uuid().'.webp';
        Storage::disk('public')->put($path, array_values($image->variants)[0] ?? '');

        return $path;
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
            Storage::disk('public')->delete($path);
        }
    }
}

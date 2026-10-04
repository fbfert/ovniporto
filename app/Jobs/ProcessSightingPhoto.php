<?php

namespace App\Jobs;

use App\Domain\Sightings\Contracts\ImageProcessor;
use App\Domain\Sightings\Contracts\PhotoStorage;
use App\Domain\Sightings\Contracts\SightingWriteRepository;
use App\Domain\Sightings\SightingPhotoFiles;
use App\Domain\Sightings\UnsupportedImage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Second metadata layer: even though the browser already sent a clean file,
 * the server re-encodes every photo from its pixels (no EXIF/XMP/IPTC can
 * survive), writes WebP variants and deletes the original upload.
 */
class ProcessSightingPhoto implements ShouldQueue
{
    use Queueable;

    public const WIDTHS = [400, 800, 1200, 1600];

    public int $tries = 3;

    public function __construct(public readonly int $photoId) {}

    public function handle(SightingWriteRepository $sightings, PhotoStorage $storage, ImageProcessor $processor): void
    {
        $photo = $sightings->findPhoto($this->photoId);
        if ($photo === null || ! $storage->exists($photo['path'])) {
            return;
        }

        try {
            $image = $processor->process($storage->get($photo['path']), self::WIDTHS);
        } catch (UnsupportedImage $e) {
            // An undecodable file never stays around with its metadata.
            report($e);
            $storage->delete($photo['path']);
            $sightings->deletePhoto($this->photoId);

            return;
        }

        $base = "sightings/{$photo['sightingId']}/{$this->photoId}";
        foreach ($image->variants as $width => $webp) {
            $storage->put("{$base}-{$width}.webp", $webp);
        }
        foreach ($image->avif as $width => $avif) {
            $storage->put("{$base}-{$width}.avif", $avif);
        }
        $storage->put("{$base}-".SightingPhotoFiles::PLACEHOLDER_WIDTH.'.webp', $image->placeholder);
        $storage->delete($photo['path']);
        $sightings->markPhotoProcessed(
            $this->photoId,
            $base,
            $image->width,
            $image->height,
            array_keys($image->variants),
            avif: count($image->avif) === count($image->variants),
        );
    }
}

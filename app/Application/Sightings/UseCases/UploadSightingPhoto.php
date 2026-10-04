<?php

namespace App\Application\Sightings\UseCases;

use App\Domain\Sightings\Contracts\ImageProcessor;
use App\Domain\Sightings\Contracts\PhotoStorage;
use App\Domain\Sightings\Contracts\SightingWriteRepository;
use App\Domain\Sightings\UnsupportedImage;
use Illuminate\Support\Str;

/**
 * Step one of the two-step upload: the photo is re-encoded without any metadata
 * (EXIF, XMP, IPTC) before it touches the disk, then gets an id in the private area.
 */
final readonly class UploadSightingPhoto
{
    public function __construct(
        private PhotoStorage $storage,
        private SightingWriteRepository $sightings,
        private ImageProcessor $images,
    ) {}

    /** @throws UnsupportedImage when the file isn't a photo the server can read */
    public function execute(int $memberId, string $contents): string
    {
        $clean = $this->images->sanitize($contents);
        $path = "uploads/{$memberId}/".Str::uuid()->toString().'.jpg';
        $this->storage->put($path, $clean);

        return $this->sightings->recordUpload($memberId, $path, 'image/jpeg', strlen($clean));
    }

    public function discard(int $memberId, string $uploadId): void
    {
        $upload = $this->sightings->findUpload($memberId, $uploadId);
        if ($upload === null) {
            return;
        }
        $this->storage->delete($upload['path']);
        $this->sightings->deleteUpload($upload['id']);
    }
}

<?php

namespace App\Application\Sightings\UseCases;

use App\Domain\Sightings\Contracts\PhotoStorage;
use App\Domain\Sightings\Contracts\SightingWriteRepository;
use Illuminate\Support\Str;

/** Step one of the two-step upload: the file goes to the private temporary area and gets an id. */
final readonly class UploadSightingPhoto
{
    private const EXTENSIONS = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/heic' => 'heic', 'image/heif' => 'heic'];

    public function __construct(
        private PhotoStorage $storage,
        private SightingWriteRepository $sightings,
    ) {}

    public function execute(int $memberId, string $contents, string $mime): string
    {
        $extension = self::EXTENSIONS[$mime] ?? 'bin';
        $path = "uploads/{$memberId}/".Str::uuid()->toString().".{$extension}";
        $this->storage->put($path, $contents);

        return $this->sightings->recordUpload($memberId, $path, $mime, strlen($contents));
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

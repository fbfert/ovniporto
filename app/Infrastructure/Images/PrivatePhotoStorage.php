<?php

namespace App\Infrastructure\Images;

use App\Domain\Sightings\Contracts\PhotoStorage;
use Illuminate\Support\Facades\Storage;

/** The private "local" disk (storage/app/private): never under public/, never linked. */
final class PrivatePhotoStorage implements PhotoStorage
{
    private const DISK = 'local';

    public function put(string $path, string $contents): void
    {
        Storage::disk(self::DISK)->put($path, $contents);
    }

    public function get(string $path): string
    {
        return (string) Storage::disk(self::DISK)->get($path);
    }

    public function delete(string $path): void
    {
        Storage::disk(self::DISK)->delete($path);
    }

    public function exists(string $path): bool
    {
        return Storage::disk(self::DISK)->exists($path);
    }
}

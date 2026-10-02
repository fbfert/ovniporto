<?php

namespace App\Infrastructure\Region;

use App\Domain\Region\Contracts\ConsentProofStorage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/** The "local" disk is private (serve: false): nothing here has a public URL. */
final class PrivateConsentProofStorage implements ConsentProofStorage
{
    private const FOLDER = 'consents';

    public function put(string $contents, string $extension): string
    {
        $path = self::FOLDER.'/'.Str::uuid().'.'.strtolower($extension);
        Storage::disk('local')->put($path, $contents);

        return $path;
    }

    public function get(string $path): ?string
    {
        return Storage::disk('local')->get($path);
    }

    public function delete(?string $path): void
    {
        if ($path !== null && str_starts_with($path, self::FOLDER.'/')) {
            Storage::disk('local')->delete($path);
        }
    }
}

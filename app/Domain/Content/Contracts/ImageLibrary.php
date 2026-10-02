<?php

namespace App\Domain\Content\Contracts;

/**
 * Images the panel publishes (terrain photos, concepts, covers, logos):
 * re-encoded from their pixels, so no EXIF or GPS from the camera survives,
 * and stored where the public site can serve them.
 */
interface ImageLibrary
{
    /** @return string the stored path */
    public function store(string $contents, string $folder): string;

    public function delete(?string $path): void;
}

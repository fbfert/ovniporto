<?php

namespace App\Domain\Sightings\Contracts;

/** Private storage for report photos. Nothing here is reachable by a public URL. */
interface PhotoStorage
{
    public function put(string $path, string $contents): void;

    public function get(string $path): string;

    public function delete(string $path): void;

    public function exists(string $path): bool;
}

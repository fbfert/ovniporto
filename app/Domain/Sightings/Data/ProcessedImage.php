<?php

namespace App\Domain\Sightings\Data;

final readonly class ProcessedImage
{
    /** @param array<int, string> $variants WebP bytes keyed by width */
    public function __construct(
        public int $width,
        public int $height,
        public array $variants,
    ) {}
}

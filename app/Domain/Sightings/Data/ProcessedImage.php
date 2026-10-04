<?php

namespace App\Domain\Sightings\Data;

final readonly class ProcessedImage
{
    /**
     * @param  array<int, string>  $variants  WebP bytes keyed by width
     * @param  array<int, string>  $avif  AVIF bytes keyed by width (empty where the server can't encode AVIF)
     * @param  string  $placeholder  20 px WebP, blurred by the page while the real image loads
     */
    public function __construct(
        public int $width,
        public int $height,
        public array $variants,
        public array $avif = [],
        public string $placeholder = '',
    ) {}
}

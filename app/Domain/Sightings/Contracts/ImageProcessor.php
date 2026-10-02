<?php

namespace App\Domain\Sightings\Contracts;

use App\Domain\Sightings\Data\ProcessedImage;
use App\Domain\Sightings\UnsupportedImage;

/**
 * Re-encodes a photo from its pixels only: no EXIF, XMP, IPTC or ICC block
 * survives. Orientation is applied to the pixels before it is thrown away.
 */
interface ImageProcessor
{
    /**
     * @param  list<int>  $widths  widths of the WebP variants (never upscaled)
     *
     * @throws UnsupportedImage when the format can't be decoded
     */
    public function process(string $binary, array $widths): ProcessedImage;
}

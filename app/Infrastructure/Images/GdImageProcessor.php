<?php

namespace App\Infrastructure\Images;

use App\Domain\Sightings\Contracts\ImageProcessor;
use App\Domain\Sightings\Data\ProcessedImage;
use App\Domain\Sightings\UnsupportedImage;
use GdImage;

/**
 * GD keeps pixels only: decoding into a GdImage and encoding again drops every
 * EXIF, XMP, IPTC and ICC block. The JPEG orientation is applied to the pixels
 * first, so phone photos aren't saved sideways. HEIC can't be decoded by GD:
 * the browser converts it before upload; a raw HEIC that reaches us is refused.
 */
final class GdImageProcessor implements ImageProcessor
{
    private const WEBP_QUALITY = 80;

    private const AVIF_QUALITY = 55;

    /** 0 (slowest, smallest) to 10: 6 keeps a 1600 px photo well under a second. */
    private const AVIF_SPEED = 6;

    public const PLACEHOLDER_WIDTH = 20;

    /** High, so the clean copy kept until processing loses nothing visible. */
    private const JPEG_QUALITY = 92;

    public function sanitize(string $binary): string
    {
        $image = $this->decode($binary);
        ob_start();
        imagejpeg($image, null, self::JPEG_QUALITY);

        return (string) ob_get_clean();
    }

    public function process(string $binary, array $widths): ProcessedImage
    {
        $image = $this->decode($binary);

        $width = imagesx($image);
        $height = imagesy($image);
        $variants = [];
        $avif = [];
        foreach ($widths as $target) {
            // Never upscaled: a target larger than the photo becomes the photo's own width, once.
            $w = min($target, $width);
            if (isset($variants[$w])) {
                continue;
            }
            $resized = $this->resize($image, $w);
            $variants[$w] = $this->webp($resized);
            if (self::canEncodeAvif()) {
                $avif[$w] = $this->avif($resized);
            }
        }

        return new ProcessedImage($width, $height, $variants, $avif, $this->webp($this->resize($image, self::PLACEHOLDER_WIDTH)));
    }

    public static function canEncodeAvif(): bool
    {
        return function_exists('imageavif') && (gd_info()['AVIF Support'] ?? false);
    }

    /** Pixels only, upright: GD never carries EXIF/XMP/IPTC/ICC into a new encoding. */
    private function decode(string $binary): GdImage
    {
        $image = @imagecreatefromstring($binary);
        if ($image === false) {
            throw new UnsupportedImage('The photo format could not be decoded.');
        }

        return $this->upright($image, JpegOrientation::read($binary));
    }

    private function upright(GdImage $image, int $orientation): GdImage
    {
        $rotated = match ($orientation) {
            3 => imagerotate($image, 180, 0),
            6 => imagerotate($image, -90, 0),
            8 => imagerotate($image, 90, 0),
            default => $image,
        };
        if ($rotated === false) {
            return $image;
        }
        if (in_array($orientation, [2, 4, 5, 7], true)) {
            imageflip($rotated, $orientation === 4 ? IMG_FLIP_VERTICAL : IMG_FLIP_HORIZONTAL);
        }

        return $rotated;
    }

    private function resize(GdImage $image, int $width): GdImage
    {
        if ($width === imagesx($image)) {
            return $image;
        }
        $height = (int) round(imagesy($image) * $width / imagesx($image));
        $target = imagecreatetruecolor($width, $height);
        if ($target === false) {
            throw new UnsupportedImage('GD could not allocate the resized photo.');
        }
        imagecopyresampled($target, $image, 0, 0, 0, 0, $width, $height, imagesx($image), imagesy($image));

        return $target;
    }

    private function webp(GdImage $image): string
    {
        ob_start();
        imagewebp($image, null, self::WEBP_QUALITY);

        return (string) ob_get_clean();
    }

    private function avif(GdImage $image): string
    {
        ob_start();
        imageavif($image, null, self::AVIF_QUALITY, self::AVIF_SPEED);

        return (string) ob_get_clean();
    }
}

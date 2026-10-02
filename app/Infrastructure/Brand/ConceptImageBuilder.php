<?php

namespace App\Infrastructure\Brand;

use GdImage;
use RuntimeException;

/**
 * Turns one concept illustration into web derivatives: AVIF and WebP at the
 * standard widths (never upscaled), a 1200px JPEG fallback and a tiny WebP
 * LQIP. GD re-encodes every pixel, so no EXIF/XMP from the source survives.
 */
final class ConceptImageBuilder
{
    public const WIDTHS = [400, 800, 1200, 1600];

    private const FALLBACK_WIDTH = 1200;

    private const LQIP_WIDTH = 20;

    public function __construct(private readonly string $outputDir) {}

    /** @return array{width: int, height: int, widths: list<int>, lqip: string} */
    public function build(string $sourcePath, string $slug): array
    {
        $source = $this->load($sourcePath);
        $width = imagesx($source);
        $height = imagesy($source);

        $widths = array_values(array_filter(self::WIDTHS, fn (int $w) => $w <= $width));
        foreach ($widths as $target) {
            $resized = $this->resize($source, $target);
            imageavif($resized, "{$this->outputDir}/{$slug}-{$target}.avif", 50, 6);
            imagewebp($resized, "{$this->outputDir}/{$slug}-{$target}.webp", 72);
        }

        imagejpeg($this->resize($source, min($width, self::FALLBACK_WIDTH)), "{$this->outputDir}/{$slug}.jpg", 80);

        return [
            'width' => $width,
            'height' => $height,
            'widths' => $widths,
            'lqip' => $this->lqip($source),
        ];
    }

    private function load(string $path): GdImage
    {
        $bytes = @file_get_contents($path);
        $image = $bytes === false ? false : @imagecreatefromstring($bytes);
        if ($image === false) {
            throw new RuntimeException("Could not read image: {$path}");
        }

        return $image;
    }

    private function resize(GdImage $source, int $width): GdImage
    {
        $height = (int) round(imagesy($source) * $width / imagesx($source));
        $target = imagecreatetruecolor($width, $height);
        if ($target === false) {
            throw new RuntimeException('GD could not allocate the resized image.');
        }
        imagecopyresampled($target, $source, 0, 0, 0, 0, $width, $height, imagesx($source), imagesy($source));

        return $target;
    }

    private function lqip(GdImage $source): string
    {
        ob_start();
        imagewebp($this->resize($source, self::LQIP_WIDTH), null, 40);

        return 'data:image/webp;base64,'.base64_encode((string) ob_get_clean());
    }
}

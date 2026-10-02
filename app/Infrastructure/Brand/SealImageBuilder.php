<?php

namespace App\Infrastructure\Brand;

use GdImage;
use RuntimeException;

/**
 * Cuts the round sticker out of its square artwork: crops to the circle, makes
 * everything outside it transparent with an anti-aliased edge (4× supersampled
 * mask) and writes WebP and AVIF at the seal sizes plus a PNG fallback.
 */
final class SealImageBuilder
{
    public const SIZES = [128, 256, 512, 768];

    /** Size of the PNG fallback (seal.png). */
    private const FALLBACK_SIZE = 512;

    private const SUPERSAMPLE = 4;

    public function __construct(private readonly string $outputDir) {}

    /** @return list<int> sizes written */
    public function build(string $sourcePath, int $centerX, int $centerY, int $radius): array
    {
        $bytes = @file_get_contents($sourcePath);
        $source = $bytes === false ? false : @imagecreatefromstring($bytes);
        if ($source === false) {
            throw new RuntimeException("Could not read sticker: {$sourcePath}");
        }

        $diameter = $radius * 2;
        $cut = $this->cutCircle($source, $centerX - $radius, $centerY - $radius, $diameter);

        $written = [];
        foreach (self::SIZES as $size) {
            if ($size > $diameter) {
                continue;
            }
            $resized = $this->transparentCanvas($size);
            imagecopyresampled($resized, $cut, 0, 0, 0, 0, $size, $size, $diameter, $diameter);
            imagewebp($resized, "{$this->outputDir}/seal-{$size}.webp", 85);
            imageavif($resized, "{$this->outputDir}/seal-{$size}.avif", 60, 6);
            if ($size === self::FALLBACK_SIZE) {
                imagepng($resized, "{$this->outputDir}/seal.png", 9);
            }
            $written[] = $size;
        }

        return $written;
    }

    private function cutCircle(GdImage $source, int $left, int $top, int $diameter): GdImage
    {
        $out = $this->transparentCanvas($diameter);
        $alpha = $this->edgeAlpha($diameter);
        imagealphablending($out, false);

        for ($y = 0; $y < $diameter; $y++) {
            for ($x = 0; $x < $diameter; $x++) {
                $coverage = $alpha[$y][$x];
                if ($coverage === 0.0) {
                    continue;
                }
                $rgb = imagecolorat($source, $left + $x, $top + $y);
                $gdAlpha = (int) round(127 * (1 - $coverage));
                imagesetpixel($out, $x, $y, ($gdAlpha << 24) | ($rgb & 0xFFFFFF));
            }
        }

        return $out;
    }

    /**
     * Per-pixel coverage of the circle, 0..1, sampled on a SUPERSAMPLE grid so the edge is smooth.
     *
     * @return array<int, array<int, float>>
     */
    private function edgeAlpha(int $diameter): array
    {
        $radius = $diameter / 2;
        $step = 1 / self::SUPERSAMPLE;
        $samples = self::SUPERSAMPLE ** 2;
        $alpha = [];

        for ($y = 0; $y < $diameter; $y++) {
            for ($x = 0; $x < $diameter; $x++) {
                $inside = 0;
                for ($sy = 0; $sy < self::SUPERSAMPLE; $sy++) {
                    for ($sx = 0; $sx < self::SUPERSAMPLE; $sx++) {
                        $dx = $x + ($sx + 0.5) * $step - $radius;
                        $dy = $y + ($sy + 0.5) * $step - $radius;
                        $inside += ($dx * $dx + $dy * $dy) <= $radius * $radius ? 1 : 0;
                    }
                }
                $alpha[$y][$x] = $inside / $samples;
            }
        }

        return $alpha;
    }

    private function transparentCanvas(int $size): GdImage
    {
        $image = imagecreatetruecolor($size, $size);
        if ($image === false) {
            throw new RuntimeException('GD could not allocate the seal canvas.');
        }
        imagealphablending($image, false);
        imagesavealpha($image, true);
        imagefill($image, 0, 0, (int) imagecolorallocatealpha($image, 0, 0, 0, 127));

        return $image;
    }
}

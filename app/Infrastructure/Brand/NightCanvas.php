<?php

namespace App\Infrastructure\Brand;

use GdImage;
use RuntimeException;

/**
 * Small GD toolkit to paint the brand night (gradient sky, stars, serra ridge,
 * green beam) for generated placeholders: OG image and demo sighting photos.
 * Only brand token colors are used.
 */
final class NightCanvas
{
    public const MOONLIGHT = [244, 245, 232];

    public const NIGHT = [6, 17, 33];

    public const NIGHT_BLUE = [20, 38, 68];

    public const HORIZON = [73, 67, 131];

    public const BEAM = [84, 201, 51];

    public const BEAM_GLOW = [173, 219, 161];

    public const CAR = [252, 184, 2];

    public readonly GdImage $image;

    public function __construct(public readonly int $width, public readonly int $height, int $seed)
    {
        $image = imagecreatetruecolor($width, $height);
        if ($image === false) {
            throw new RuntimeException('GD could not allocate the canvas.');
        }
        $this->image = $image;
        imagealphablending($this->image, true);
        imagesavealpha($this->image, false);
        mt_srand($seed);
    }

    /** @param array{int, int, int} $rgb */
    public function color(array $rgb, float $opacity = 1.0): int
    {
        $alpha = (int) round(127 * (1 - max(0.0, min(1.0, $opacity))));

        return (int) imagecolorallocatealpha($this->image, $rgb[0], $rgb[1], $rgb[2], $alpha);
    }

    public function sky(): self
    {
        for ($y = 0; $y < $this->height; $y++) {
            $t = $y / $this->height;
            $rgb = [
                (int) (self::NIGHT_BLUE[0] + (self::NIGHT[0] - self::NIGHT_BLUE[0]) * $t),
                (int) (self::NIGHT_BLUE[1] + (self::NIGHT[1] - self::NIGHT_BLUE[1]) * $t),
                (int) (self::NIGHT_BLUE[2] + (self::NIGHT[2] - self::NIGHT_BLUE[2]) * $t),
            ];
            imageline($this->image, 0, $y, $this->width, $y, $this->color($rgb));
        }

        return $this;
    }

    /** Paints an illustration cover-cropped to the canvas (focus = vertical crop anchor, 0 top … 1 bottom), under a night veil. */
    public function backdrop(string $path, float $veil = 0.3, float $focus = 0.5): self
    {
        $bytes = @file_get_contents($path);
        $source = $bytes === false ? false : @imagecreatefromstring($bytes);
        if ($source === false) {
            throw new RuntimeException("Could not read backdrop: {$path}");
        }

        $scale = max($this->width / imagesx($source), $this->height / imagesy($source));
        $cropW = (int) round($this->width / $scale);
        $cropH = (int) round($this->height / $scale);
        $srcX = (int) ((imagesx($source) - $cropW) / 2);
        $srcY = (int) ((imagesy($source) - $cropH) * $focus);
        imagecopyresampled($this->image, $source, 0, 0, $srcX, $srcY, $this->width, $this->height, $cropW, $cropH);
        imagefilledrectangle($this->image, 0, 0, $this->width, $this->height, $this->color(self::NIGHT, $veil));

        return $this;
    }

    /** Draws a (transparent) image scaled to $size×$size with its top-left at ($x, $y). */
    public function overlay(string $path, int $x, int $y, int $size): self
    {
        $bytes = @file_get_contents($path);
        $source = $bytes === false ? false : @imagecreatefromstring($bytes);
        if ($source === false) {
            throw new RuntimeException("Could not read overlay: {$path}");
        }
        imagecopyresampled($this->image, $source, $x, $y, 0, 0, $size, $size, imagesx($source), imagesy($source));

        return $this;
    }

    public function haze(float $fromRatio = 0.5): self
    {
        $start = (int) ($this->height * $fromRatio);
        for ($y = $start; $y < $this->height; $y++) {
            $t = ($y - $start) / max(1, $this->height - $start);
            imageline($this->image, 0, $y, $this->width, $y, $this->color(self::HORIZON, 0.45 * $t));
        }

        return $this;
    }

    public function stars(int $count): self
    {
        for ($i = 0; $i < $count; $i++) {
            $x = mt_rand(0, $this->width);
            $y = mt_rand(0, (int) ($this->height * 0.75));
            $size = mt_rand(0, 100) > 88 ? 3 : (mt_rand(0, 100) > 60 ? 2 : 1);
            $tint = mt_rand(0, 100) > 90 ? self::BEAM_GLOW : self::MOONLIGHT;
            imagefilledellipse($this->image, $x, $y, $size, $size, $this->color($tint, mt_rand(45, 95) / 100));
        }

        return $this;
    }

    /** A soft point of light with a halo: the thing someone swore they saw. */
    public function light(int $x, int $y, int $radius): self
    {
        for ($r = $radius * 5; $r > $radius; $r -= 2) {
            $opacity = 0.05 * (1 - ($r - $radius) / ($radius * 4));
            imagefilledellipse($this->image, $x, $y, $r, $r, $this->color(self::BEAM, $opacity));
        }
        imagefilledellipse($this->image, $x, $y, $radius, $radius, $this->color(self::BEAM_GLOW));

        return $this;
    }

    public function beam(int $topX, int $topY, int $bottomY, int $topHalf, int $bottomHalf, float $opacity): self
    {
        imagefilledpolygon($this->image, [
            $topX - $topHalf, $topY,
            $topX + $topHalf, $topY,
            $topX + $bottomHalf, $bottomY,
            $topX - $bottomHalf, $bottomY,
        ], $this->color(self::BEAM, $opacity));

        return $this;
    }

    /** @param array{int, int, int} $rgb */
    public function ridge(float $baseRatio, int $amplitude, array $rgb): self
    {
        $points = [0, $this->height];
        $phase = mt_rand(0, 628) / 100;
        for ($x = 0; $x <= $this->width; $x += 20) {
            $y = $this->height * $baseRatio
                + sin($x / $this->width * 5 + $phase) * $amplitude
                + sin($x / $this->width * 13 + $phase * 2) * $amplitude * 0.35;
            array_push($points, $x, (int) $y);
        }
        array_push($points, $this->width, $this->height);
        imagefilledpolygon($this->image, $points, $this->color($rgb));

        return $this;
    }

    /** @param array{int, int, int} $rgb */
    public function text(string $text, string $font, float $size, int $y, array $rgb, float $opacity = 1.0, float $spacing = 0): self
    {
        $color = $this->color($rgb, $opacity);

        if ($spacing <= 0) {
            $box = imagettfbbox($size, 0, $font, $text) ?: [0, 0, 0, 0, 0, 0, 0, 0];
            imagettftext($this->image, $size, 0, (int) (($this->width - ($box[2] - $box[0])) / 2) - $box[0], $y, $color, $font, $text);

            return $this;
        }

        $chars = mb_str_split($text);
        $advances = array_map(function (string $char) use ($size, $font): int {
            $box = imagettfbbox($size, 0, $font, $char) ?: [0, 0, 0, 0, 0, 0, 0, 0];

            return $box[2] - $box[0];
        }, $chars);
        $x = (int) (($this->width - array_sum($advances) - $spacing * (count($chars) - 1)) / 2);

        foreach ($chars as $i => $char) {
            imagettftext($this->image, $size, 0, $x, $y, $color, $font, $char);
            $x += (int) ($advances[$i] + $spacing);
        }

        return $this;
    }

    public function saveJpeg(string $path, int $quality = 86): void
    {
        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }
        imagejpeg($this->image, $path, $quality);
    }
}

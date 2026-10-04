<?php

namespace App\Console\Commands;

use App\Infrastructure\Brand\NightCanvas;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('brand:postal')]
#[Description('Generate public/brand/postal.jpg (1500×1000, the postcard people download) and postal-og.jpg (1200×630, its link preview)')]
class GeneratePostcardImage extends Command
{
    public const MESSAGE = 'Guardei um lugar pra você.';

    /** Widths the page's srcset points to (resources/js/Components/Postcard/FlipPostcard.tsx). */
    public const WEBP_WIDTHS = [800, 1500];

    public function handle(): int
    {
        $front = $this->paint(1500, 1000);
        $front->saveJpeg(public_path('brand/postal.jpg'), 88);
        foreach (self::WEBP_WIDTHS as $width) {
            $front->saveWebp(public_path("brand/postal-{$width}.webp"), $width);
        }
        $this->paint(1200, 630)->saveJpeg(public_path('brand/postal-og.jpg'));
        $this->info('public/brand gerado: postal.jpg, WebP '.implode('/', self::WEBP_WIDTHS).' e postal-og.jpg.');

        return self::SUCCESS;
    }

    /** The front of the postcard: the concept cover, the sticker, the city and the closing line. */
    private function paint(int $width, int $height): NightCanvas
    {
        $fonts = resource_path('fonts');
        $scale = $height / 1000;
        $canvas = new NightCanvas($width, $height, seed: 2028);
        $cover = public_path('concept/cover-1600.webp');

        if (is_file($cover)) {
            $canvas->backdrop($cover, veil: 0.2, focus: 0.4)->fade(0.45, 0.85);
        } else {
            $canvas->sky()->stars(320)->haze(0.5)->ridge(0.8, 20, NightCanvas::NIGHT_BLUE)->ridge(0.9, 10, NightCanvas::NIGHT);
        }

        $seal = public_path('brand/seal-512.webp');
        $sealSize = (int) (250 * $scale);
        if (is_file($seal)) {
            // Top right, where a real postcard carries its stamp.
            $canvas->overlay($seal, $width - $sealSize - (int) (64 * $scale), (int) (56 * $scale), $sealSize);
        }

        return $canvas
            ->text(self::MESSAGE, "{$fonts}/Caveat.ttf", 78 * $scale, (int) ($height - 230 * $scale), NightCanvas::BEAM_GLOW)
            ->text('LAGES · SC', "{$fonts}/Unbounded.ttf", 40 * $scale, (int) ($height - 130 * $scale), NightCanvas::MOONLIGHT, spacing: 10 * $scale)
            ->text('ovniporto.tars.art.br  ·  ilustração conceito', "{$fonts}/Figtree.ttf", 17 * $scale, (int) ($height - 60 * $scale), NightCanvas::MOONLIGHT, 0.7, spacing: 2 * $scale);
    }
}

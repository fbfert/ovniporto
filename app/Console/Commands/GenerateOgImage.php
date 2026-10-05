<?php

namespace App\Console\Commands;

use App\Infrastructure\Brand\NightCanvas;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('brand:og')]
#[Description('Generate public/og/default.jpg (1200×630): the sticker over the cover illustration (falls back to the drawn night scene)')]
class GenerateOgImage extends Command
{
    public function handle(): int
    {
        $fonts = resource_path('fonts');
        $canvas = new NightCanvas(1200, 630, seed: 2028);
        $cover = public_path('concept/cover-1600.webp');
        $seal = public_path('brand/seal-512.webp');

        if (is_file($cover) && is_file($seal)) {
            $canvas->backdrop($cover, veil: 0.4, focus: 0.35)
                ->overlay($seal, 600 - 220, 30, 440)
                ->text('A pista de pouso do planalto', "{$fonts}/Caveat.ttf", 44, 530, NightCanvas::BEAM_GLOW)
                ->text('LAGES · SC  ·  CONCEITO', "{$fonts}/Figtree.ttf", 18, 600, NightCanvas::MOONLIGHT, 0.85, spacing: 5);
        } else {
            $this->paintDrawnScene($canvas, $fonts);
        }

        $canvas->saveJpeg(public_path('og/default.jpg'));
        $this->info('public/og/default.jpg gerado.');

        return self::SUCCESS;
    }

    private function paintDrawnScene(NightCanvas $canvas, string $fonts): void
    {
        $canvas->sky()
            ->stars(260)
            ->haze(0.45)
            ->beam(600, 0, 470, 26, 150, 0.16)
            ->beam(600, 0, 470, 8, 54, 0.12)
            ->ridge(0.78, 18, NightCanvas::NIGHT_BLUE)
            ->ridge(0.88, 10, NightCanvas::NIGHT)
            ->text('Vigília grátis', "{$fonts}/Caveat.ttf", 34, 150, NightCanvas::BEAM_GLOW)
            ->text('OVNIPORTO', "{$fonts}/Unbounded.ttf", 96, 300, NightCanvas::MOONLIGHT, spacing: 6)
            ->text('A pista de pouso do planalto', "{$fonts}/Caveat.ttf", 46, 380, NightCanvas::BEAM_GLOW)
            ->text('LAGES · SANTA CATARINA', "{$fonts}/Figtree.ttf", 20, 590, NightCanvas::MOONLIGHT, 0.75, spacing: 5);

        $this->paintCar($canvas, 600, 552);
    }

    /** The yellow car, side view, standing on ($x, $groundY). */
    private function paintCar(NightCanvas $canvas, int $x, int $groundY): void
    {
        $car = $canvas->color(NightCanvas::CAR);
        $glass = $canvas->color(NightCanvas::NIGHT_BLUE);
        $tyre = $canvas->color(NightCanvas::NIGHT);
        $hub = $canvas->color(NightCanvas::MOONLIGHT, 0.8);

        imagefilledellipse($canvas->image, $x, $groundY - 4, 190, 14, $canvas->color(NightCanvas::BEAM, 0.25));
        imagefilledarc($canvas->image, $x - 4, $groundY - 22, 104, 92, 180, 360, $car, IMG_ARC_PIE);
        imagefilledrectangle($canvas->image, $x - 70, $groundY - 34, $x + 70, $groundY - 12, $car);
        imagefilledellipse($canvas->image, $x - 62, $groundY - 23, 26, 24, $car);
        imagefilledellipse($canvas->image, $x + 62, $groundY - 23, 26, 24, $car);
        imagefilledarc($canvas->image, $x - 4, $groundY - 34, 76, 58, 200, 340, $glass, IMG_ARC_PIE);
        imagefilledrectangle($canvas->image, $x - 6, $groundY - 64, $x - 2, $groundY - 34, $car);
        foreach ([-40, 40] as $offset) {
            imagefilledellipse($canvas->image, $x + $offset, $groundY - 10, 26, 26, $tyre);
            imagefilledellipse($canvas->image, $x + $offset, $groundY - 10, 10, 10, $hub);
        }
        imagefilledellipse($canvas->image, $x + 68, $groundY - 26, 7, 7, $canvas->color(NightCanvas::MOONLIGHT));
    }
}

<?php

namespace App\Console\Commands;

use App\Infrastructure\Brand\NightCanvas;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('brand:icons')]
#[Description('Generate the install icons in public/icons from the sticker (192, 512, maskable 512, apple-touch 180)')]
class GenerateAppIcons extends Command
{
    /** name => [size, share of the side the sticker takes] (maskable keeps it inside the 80% safe zone). */
    private const ICONS = [
        'icon-192.png' => [192, 0.92],
        'icon-512.png' => [512, 0.92],
        'icon-maskable-512.png' => [512, 0.7],
        'apple-touch-icon.png' => [180, 0.86],
    ];

    public function handle(): int
    {
        $seal = public_path('brand/seal-512.webp');
        if (! is_file($seal)) {
            $this->error('public/brand/seal-512.webp não existe: rode brand:seal antes.');

            return self::FAILURE;
        }

        foreach (self::ICONS as $name => [$size, $share]) {
            $canvas = new NightCanvas($size, $size, seed: 2028);
            imagefilledrectangle($canvas->image, 0, 0, $size, $size, $canvas->color(NightCanvas::NIGHT));
            $side = (int) round($size * $share);
            $offset = intdiv($size - $side, 2);
            $canvas->overlay($seal, $offset, $offset, $side);
            $this->savePng($canvas, public_path("icons/{$name}"));
        }
        $this->info('public/icons gerado: '.implode(', ', array_keys(self::ICONS)).'.');

        return self::SUCCESS;
    }

    private function savePng(NightCanvas $canvas, string $path): void
    {
        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }
        imagepng($canvas->image, $path, 9);
    }
}

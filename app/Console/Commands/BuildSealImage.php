<?php

namespace App\Console\Commands;

use App\Infrastructure\Brand\SealImageBuilder;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Defaults match "adesivo ovniporto.png" (1024×1024): the printed circle sits
 * slightly off-center on a grey square. Re-measure if the artwork changes.
 */
#[Signature('brand:seal {source : The sticker artwork (square PNG/JPEG)} {--cx=515} {--cy=508} {--r=393} {--output= : Output folder}')]
#[Description('Cut the round sticker out of its artwork into public/brand/seal-*.{webp,avif} and seal.png')]
class BuildSealImage extends Command
{
    public function handle(): int
    {
        $output = (string) ($this->option('output') ?: public_path('brand'));
        File::ensureDirectoryExists($output);

        $sizes = (new SealImageBuilder($output))->build(
            (string) $this->argument('source'),
            (int) $this->option('cx'),
            (int) $this->option('cy'),
            (int) $this->option('r'),
        );

        $this->info('Selo gerado em '.implode(', ', $sizes).' px.');

        return self::SUCCESS;
    }
}

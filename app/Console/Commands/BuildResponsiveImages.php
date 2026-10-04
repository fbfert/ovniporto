<?php

namespace App\Console\Commands;

use App\Infrastructure\Images\PublicImageLibrary;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

#[Signature('images:responsive')]
#[Description('Write the AVIF/WebP widths and the placeholder for panel images stored before they existed')]
class BuildResponsiveImages extends Command
{
    /** Folders PublicImageLibrary writes to (products, partners, diary, place, sponsors). */
    private const PATTERN = '#^(?!demo/)[a-z0-9/_-]+/[0-9a-f-]{36}\.webp$#';

    public function handle(PublicImageLibrary $library): int
    {
        $done = 0;
        foreach (Storage::disk('public')->allFiles() as $path) {
            if (preg_match(self::PATTERN, $path) === 1 && ! PublicImageLibrary::hasVariants($path) && $library->backfill($path)) {
                $done++;
            }
        }
        $this->info("{$done} imagem(ns) com variantes novas.");

        return self::SUCCESS;
    }
}

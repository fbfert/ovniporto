<?php

namespace App\Console\Commands;

use App\Infrastructure\Brand\ConceptImageBuilder;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

#[Signature('origin:images {source : Folder with the original files named as in images.json} {--credits= : Credits JSON path} {--output= : Derivatives folder} {--manifest= : Manifest JSON path}')]
#[Description('Build AVIF/WebP/JPEG derivatives and the front-end manifest for the licensed images of /origem')]
class ImportOriginImages extends Command
{
    public function handle(): int
    {
        $source = rtrim((string) $this->argument('source'), '/\\');
        $creditsPath = (string) ($this->option('credits') ?: resource_path('content/origin/images.json'));
        $output = (string) ($this->option('output') ?: public_path('origin'));
        $manifestPath = (string) ($this->option('manifest') ?: resource_path('js/data/origin-images.json'));

        /** @var list<array{slug: string, original: string}> $credits */
        $credits = json_decode(File::get($creditsPath), true, flags: JSON_THROW_ON_ERROR);
        File::ensureDirectoryExists($output);
        $builder = new ConceptImageBuilder($output);
        $manifest = [];
        $missing = [];

        foreach ($credits as $image) {
            $path = "{$source}/{$image['original']}";
            if (! is_file($path)) {
                $missing[] = $image['original'];

                continue;
            }
            $manifest[$image['slug']] = $builder->build($path, $image['slug']);
            $this->line("{$image['slug']} ← {$image['original']}");
        }

        if ($missing !== []) {
            // Every credited image must ship: a page never points at a file that is not there.
            $this->error('Arquivos ausentes: '.implode(', ', $missing));

            return self::FAILURE;
        }

        File::ensureDirectoryExists(dirname($manifestPath));
        File::put($manifestPath, json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n");
        $this->info(count($manifest).' imagens processadas.');

        return self::SUCCESS;
    }
}

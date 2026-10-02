<?php

namespace App\Console\Commands;

use App\Models\ConstructionPost;
use App\Models\RegionPartner;
use App\Models\Sighting;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

#[Signature('dev:clear-demo')]
#[Description('Local only: remove every demo sighting, photo and partner')]
class ClearDemoData extends Command
{
    public function handle(): int
    {
        if (! app()->environment(['local', 'testing'])) {
            $this->error('dev:clear-demo só roda em ambiente local.');

            return self::FAILURE;
        }

        Sighting::query()->where('is_demo', true)->delete();
        RegionPartner::query()->where('is_demo', true)->delete();
        ConstructionPost::query()->where('is_demo', true)->delete();
        Storage::disk('public')->deleteDirectory('demo');

        $this->info('Dados de demonstração removidos.');

        return self::SUCCESS;
    }
}

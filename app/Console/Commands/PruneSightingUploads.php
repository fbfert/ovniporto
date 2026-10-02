<?php

namespace App\Console\Commands;

use App\Domain\Sightings\Contracts\PhotoStorage;
use App\Domain\Sightings\Contracts\SightingWriteRepository;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('sightings:prune-uploads {--hours=24 : Age after which an unclaimed upload is deleted}')]
#[Description('Delete temporary report photos that no report claimed')]
class PruneSightingUploads extends Command
{
    public function handle(SightingWriteRepository $sightings, PhotoStorage $storage): int
    {
        $stale = $sightings->staleUploads(now()->subHours((int) $this->option('hours')));
        foreach ($stale as $upload) {
            $storage->delete($upload['path']);
            $sightings->deleteUpload($upload['id']);
        }
        $this->info(count($stale).' foto(s) temporária(s) removida(s).');

        return self::SUCCESS;
    }
}

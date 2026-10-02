<?php

namespace App\Console\Commands;

use App\Domain\Sightings\SightingStatus;
use App\Domain\Sightings\SightingType;
use App\Infrastructure\Brand\NightCanvas;
use App\Models\ConstructionPost;
use App\Models\RegionPartner;
use App\Models\Sighting;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

#[Signature('dev:seed-demo')]
#[Description('Local only: 12 approved demo sightings with generated sky photos, 3 [EXEMPLO] partners and 2 [EXEMPLO] diary posts')]
class SeedDemoData extends Command
{
    private const LAGES = [-27.81, -50.326];

    private const PLACES = ['Lages', 'Painel', 'Capão Alto', 'São José do Cerrito', 'Coxilha Rica', 'Vila das Pedras'];

    private const NICKNAMES = ['vigia_da_serra', 'coruja', 'farol', 'araucaria', 'cometa', 'neblina'];

    public function handle(): int
    {
        if (! app()->environment(['local', 'testing'])) {
            $this->error('dev:seed-demo só roda em ambiente local.');

            return self::FAILURE;
        }

        $this->call('dev:clear-demo');
        $disk = Storage::disk('public');

        foreach (range(1, 12) as $i) {
            [$lat, $lng] = $this->pointWithinKm(30, $i);
            $sighting = Sighting::query()->create([
                'type' => SightingType::cases()[$i % 4],
                'description' => '[EXEMPLO] Relato de demonstração gerado para testar o Livro de avistamentos.',
                'observed_date' => now()->subDays($i * 3)->toDateString(),
                'observed_time_kind' => 'range',
                'observed_time_range' => 'night',
                'lat' => $lat,
                'lng' => $lng,
                'place_label' => self::PLACES[$i % count(self::PLACES)],
                'public_nickname' => self::NICKNAMES[$i % count(self::NICKNAMES)],
                'consent_given_at' => now(),
                'status' => SightingStatus::Approved,
                'published_at' => now()->subDays($i),
                'is_demo' => true,
            ]);

            if ($i % 3 !== 0) {
                $path = "demo/sighting-{$sighting->id}.jpg";
                $this->paintSky($i)->saveJpeg($disk->path($path));
                $sighting->photos()->create(['path' => $path, 'width' => 800, 'height' => 1000, 'sort_order' => 0]);
            }
        }

        foreach (['Pousada [EXEMPLO]' => 'inn', 'Trilha [EXEMPLO]' => 'attraction', 'Vinícola [EXEMPLO]' => 'producer'] as $name => $type) {
            RegionPartner::query()->create([
                'name' => $name,
                'slug' => str($name)->slug()->toString(),
                'type' => $type,
                'city' => 'Lages',
                'short_description' => 'Parceiro fictício de demonstração.',
                'is_featured' => true,
                'consent_given_at' => now(),
                'published_at' => now()->subDay(),
                'is_demo' => true,
            ]);
        }

        foreach ([1 => 'Medição do terreno', 2 => 'Primeiras pedras separadas'] as $i => $title) {
            ConstructionPost::query()->create([
                'title' => "[EXEMPLO] {$title}",
                'slug' => 'exemplo-'.str($title)->slug(),
                'excerpt' => 'Post fictício de demonstração do Diário da obra.',
                'body' => "**[EXEMPLO]** Este post existe só no ambiente local, para testar o Diário da obra.\n\n## O que foi feito\nNada: a obra ainda não começou.",
                'phase' => 1,
                'published_at' => now()->subDays(10 * $i),
                'is_demo' => true,
            ]);
        }

        $this->info('Dados de demonstração criados: 12 relatos, 3 parceiros e 2 posts da obra [EXEMPLO].');

        return self::SUCCESS;
    }

    /** @return array{float, float} */
    private function pointWithinKm(float $km, int $seed): array
    {
        mt_srand($seed * 7919);
        $distance = $km * sqrt(mt_rand(0, 1000) / 1000);
        $bearing = deg2rad(mt_rand(0, 359));
        $lat = self::LAGES[0] + ($distance / 111.32) * cos($bearing);
        $lng = self::LAGES[1] + ($distance / (111.32 * cos(deg2rad(self::LAGES[0])))) * sin($bearing);

        return [round($lat, 6), round($lng, 6)];
    }

    private function paintSky(int $seed): NightCanvas
    {
        $canvas = (new NightCanvas(800, 1000, $seed))->sky()->stars(180)->haze(0.6);
        $canvas->light(mt_rand(160, 640), mt_rand(150, 520), mt_rand(10, 18));
        if ($seed % 2 === 0) {
            $canvas->light(mt_rand(160, 640), mt_rand(150, 520), 7);
        }

        return $canvas->ridge(0.86, 22, NightCanvas::NIGHT);
    }
}

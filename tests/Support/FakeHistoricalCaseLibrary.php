<?php

namespace Tests\Support;

use App\Domain\Sightings\Contracts\HistoricalCaseLibrary;

/** Three historical cases in two regions (one region empty), enough to exercise the use cases. */
final class FakeHistoricalCaseLibrary implements HistoricalCaseLibrary
{
    public function collection(): array
    {
        $case = fn (string $slug, string $region, float $lat) => [
            'slug' => $slug,
            'region' => $region,
            'title' => "Caso {$slug}",
            'date' => '01/01/2000',
            'place' => 'Lugar',
            'summary' => 'Resumo.',
            'documentation' => 'Situação.',
            'sources' => [['label' => 'Fonte', 'url' => 'https://exemplo.org']],
            'image' => "caso-{$slug}",
            'coordinates' => ['lat' => $lat, 'lng' => -48.0],
        ];

        return [
            'eyebrow' => 'Antes do Livro',
            'title' => 'Casos históricos',
            'intro' => 'Introdução.',
            'note' => 'Nota.',
            'regions' => ['sc' => 'Santa Catarina', 'brasil' => 'Brasil, outros estados', 'mundo' => 'Mundo'],
            'cases' => [$case('um', 'sc', -27.0), $case('dois', 'sc', -26.0), $case('tres', 'mundo', 43.0)],
        ];
    }

    public function slugs(): array
    {
        return array_column($this->collection()['cases'], 'slug');
    }
}

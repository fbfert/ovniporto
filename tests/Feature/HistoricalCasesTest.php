<?php

use App\Domain\Sightings\Contracts\HistoricalCaseLibrary;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    config(['app.url' => 'https://ovniporto.test', 'inertia.ssr.enabled' => false]);
    URL::forceRootUrl('https://ovniporto.test');
});

it('shows the twelve historical cases on /mapa, grouped, with their map points', function () {
    $this->get('/mapa')->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('Sightings/Logbook')
        ->where('historical.groups', fn ($groups) => collect($groups)->pluck('region')->all() === ['sc', 'brasil', 'mundo'])
        ->where('historical.groups.0.cases', fn ($cases) => count($cases) === 4)
        ->has('historical.pins', 12)
        ->etc()
    );
});

it('opens each case on its own page, with its sources and the illustration as preview', function () {
    $this->get('/mapa/casos/florianopolis-1981')->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('Sightings/HistoricalCase')
        ->where('case.title', 'A torre de controle de Florianópolis')
        ->where('region', 'Santa Catarina')
        ->where('case.sources.0.url', fn (string $url) => str_starts_with($url, 'https://imagem.sian.an.gov.br/'))
        ->where('previous', null)
        ->where('next.slug', 'picarras-2017')
        ->where('seo.title', 'A torre de controle de Florianópolis · Casos históricos')
        ->where('seo.image', 'https://ovniporto.test/concept/caso-florianopolis-1981.jpg')
        ->etc()
    );
});

it('answers 404 for a case that does not exist', function () {
    $this->get('/mapa/casos/nao-existe')->assertNotFound();
});

it('has an illustration with alt text and a source for every case', function () {
    $manifest = array_keys(json_decode((string) file_get_contents(resource_path('js/data/concept.json')), true));
    $alts = (string) file_get_contents(resource_path('js/i18n/pt-BR.ts'));

    foreach (app(HistoricalCaseLibrary::class)->collection()['cases'] as $case) {
        expect($case['image'])->toBeIn($manifest)
            ->and($alts)->toContain("'{$case['image']}':")
            ->and($case['sources'])->not->toBeEmpty();
    }
});

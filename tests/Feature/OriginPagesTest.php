<?php

use App\Domain\Origin\Contracts\OriginLibrary;
use App\Models\ContentBlock;
use Database\Seeders\DatabaseSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(fn () => $this->seed(DatabaseSeeder::class));

it('moves the former /lenda permanently to /origem', function () {
    $this->get('/lenda')->assertStatus(301)->assertRedirect('/origem');
});

it('serves the origin pages to anonymous visitors', function (string $path, string $component) {
    $this->get($path)->assertOk()->assertInertia(fn (Assert $page) => $page->component($component));
})->with([
    ['/origem', 'Origin/Hub'],
    ['/origem/cachi', 'Origin/Cachi'],
    ['/origem/atlas', 'Origin/Atlas'],
]);

it('has a page for every one of the twelve Atlas cases', function () {
    $slugs = app(OriginLibrary::class)->caseSlugs();

    expect($slugs)->toHaveCount(12);
    foreach ($slugs as $slug) {
        $this->get("/origem/atlas/{$slug}")
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Origin/AtlasCase')->where('case.slug', $slug));
    }
});

it('answers 404 for a case that is not in the Atlas', function () {
    $this->get('/origem/atlas/nao-existe')->assertNotFound();
});

it('keeps the relato of the yellow car waiting while its text is empty', function () {
    ContentBlock::query()->where('key', 'legend_body')->update(['value' => '']);

    $this->get('/origem')->assertInertia(fn (Assert $page) => $page
        ->where('relatoOpening', null)
        ->where('atlas.cases', 12)
        ->where('cachi.chapters', 14)
    );
});

it('opens the relato on /origem and tells it whole on /origem/relato', function () {
    ContentBlock::query()->where('key', 'legend_body')->update(['value' => "O Niva do Julean **subiu**.\n\nNada."]);

    $this->get('/origem')->assertInertia(fn (Assert $page) => $page
        ->where('relatoOpening', ['O Niva do Julean subiu.', 'Nada.'])
        ->etc()
    );
    $this->get('/origem/relato')->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('Origin/Relato')
        ->where('relatoHtml', fn (string $html) => str_contains($html, '<strong>subiu</strong>') && str_contains($html, '<p class="relato-beat">Nada.</p>'))
        ->etc()
    );
});

it('tells what is still missing for each candidate case', function () {
    $this->get('/origem/atlas')->assertInertia(fn (Assert $page) => $page
        ->where('candidates.0.status', 'unbuilt')
        ->where('candidates.1.status', 'inspection')
        ->where('candidates.2.status', 'verification')
        ->where('candidates', fn ($candidates) => collect($candidates)->every(fn (array $c) => count($c['pending']) > 0))
        ->etc()
    );
});

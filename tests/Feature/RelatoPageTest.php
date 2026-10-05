<?php

use App\Models\ContentBlock;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    config(['app.url' => 'https://ovniporto.test', 'inertia.ssr.enabled' => false]);
    URL::forceRootUrl('https://ovniporto.test');
});

function runRelatoMigration(): void
{
    (require database_path('migrations/2026_10_05_130000_fill_yellow_car_relato.php'))->up();
}

it('has Julean\'s relato after migrating, without any seeder', function () {
    $this->get('/origem/relato')->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('Origin/Relato')
        ->where('relatoHtml', fn (string $html) => str_contains($html, 'Era tarde da noite')
            && str_contains($html, '<p class="relato-beat">Nada.</p>')
            && str_contains($html, 'ou se tremo.'))
        ->etc()
    );
});

it('fills an empty relato and keeps one already written in the panel', function () {
    ContentBlock::query()->where('key', 'legend_body')->update(['value' => '']);
    runRelatoMigration();
    expect(ContentBlock::query()->where('key', 'legend_body')->value('value'))->toStartWith('Era tarde da noite');

    ContentBlock::query()->where('key', 'legend_body')->update(['value' => 'Editado no painel.']);
    runRelatoMigration();
    expect(ContentBlock::query()->where('key', 'legend_body')->value('value'))->toBe('Editado no painel.');
});

it('waits honestly on its own page while the relato is empty', function () {
    ContentBlock::query()->where('key', 'legend_body')->update(['value' => '']);

    $this->get('/origem/relato')->assertOk()->assertInertia(fn (Assert $page) => $page->where('relatoHtml', null)->etc());
});

it('is shared as a story told by Julean and listed for search engines', function () {
    $html = $this->get('/origem/relato')->getContent();
    preg_match('#<script type="application/ld\+json">(.*?)</script>#s', $html, $match);
    $story = json_decode($match[1], true);

    expect($html)->toContain('<title>O relato do carro amarelo do Julean · OVNIPORTO Lages</title>')
        ->and($story['@type'])->toBe('ShortStory')
        ->and($story['author']['name'])->toBe('Julean')
        ->and($story['url'])->toBe('https://ovniporto.test/origem/relato');
    $this->get('/llms.txt')->assertSee('(https://ovniporto.test/origem/relato)', false);
});

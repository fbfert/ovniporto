<?php

use App\Application\Content\UseCases\BuildSitemap;
use App\Domain\Content\Contracts\PublishedContentIndex;
use App\Domain\Content\Sharing\SitemapEntry;
use Tests\Support\FakeHistoricalCaseLibrary;
use Tests\Support\FakeOriginLibrary;

it('puts the fixed public pages first, then the Atlas cases, then the published content', function () {
    $index = new class implements PublishedContentIndex
    {
        public function entries(): array
        {
            return [new SitemapEntry('/obra/pedra', new DateTimeImmutable('2026-10-01'))];
        }
    };

    $paths = array_map(fn (SitemapEntry $e) => $e->path, (new BuildSitemap($index, new FakeOriginLibrary, new FakeHistoricalCaseLibrary))->execute());

    expect($paths[0])->toBe('/')
        ->and(end($paths))->toBe('/obra/pedra')
        ->and($paths)->toContain('/origem', '/origem/cachi', '/origem/atlas', '/origem/atlas/st-paul', '/origem/atlas/lages')
        ->not->toContain('/lenda')
        ->and($paths)->toContain('/mapa/casos/um', '/mapa/casos/tres')
        ->and($paths)->toHaveCount(count(BuildSitemap::PAGES) + 3 + 3 + 1);
});

it('never lists the members area, the panel, checkout or orders', function () {
    foreach (BuildSitemap::PAGES as $path) {
        expect($path)->not->toStartWith('/painel')->not->toStartWith('/conta')
            ->not->toStartWith('/checkout')->not->toStartWith('/pedido');
    }
});

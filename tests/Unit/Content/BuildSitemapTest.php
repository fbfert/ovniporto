<?php

use App\Application\Content\UseCases\BuildSitemap;
use App\Domain\Content\Contracts\PublishedContentIndex;
use App\Domain\Content\Sharing\SitemapEntry;

it('puts the fixed public pages first, then the published content', function () {
    $index = new class implements PublishedContentIndex
    {
        public function entries(): array
        {
            return [new SitemapEntry('/obra/pedra', new DateTimeImmutable('2026-10-01'))];
        }
    };

    $paths = array_map(fn (SitemapEntry $e) => $e->path, (new BuildSitemap($index))->execute());

    expect($paths[0])->toBe('/')
        ->and(end($paths))->toBe('/obra/pedra')
        ->and($paths)->toHaveCount(count(BuildSitemap::PAGES) + 1);
});

it('never lists the members area, the panel, checkout or orders', function () {
    foreach (BuildSitemap::PAGES as $path) {
        expect($path)->not->toStartWith('/painel')->not->toStartWith('/conta')
            ->not->toStartWith('/checkout')->not->toStartWith('/pedido');
    }
});

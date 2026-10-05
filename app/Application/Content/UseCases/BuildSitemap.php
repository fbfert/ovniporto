<?php

namespace App\Application\Content\UseCases;

use App\Domain\Content\Contracts\PublishedContentIndex;
use App\Domain\Content\Sharing\SitemapEntry;
use App\Domain\Origin\Contracts\OriginLibrary;

final readonly class BuildSitemap
{
    /** Fixed public pages. Members' area, panel, checkout and orders never enter the sitemap. */
    public const PAGES = [
        '/', '/origem', '/origem/cachi', '/origem/atlas', '/faq', '/comunidade', '/o-lugar', '/apoie', '/regiao', '/obra',
        '/mapa', '/loja', '/postal', '/privacidade', '/termos',
    ];

    public function __construct(
        private PublishedContentIndex $content,
        private OriginLibrary $origin,
    ) {}

    /** @return list<SitemapEntry> */
    public function execute(): array
    {
        return [
            ...array_map(fn (string $path) => new SitemapEntry($path), self::PAGES),
            ...array_map(fn (string $slug) => new SitemapEntry("/origem/atlas/{$slug}"), $this->origin->caseSlugs()),
            ...$this->content->entries(),
        ];
    }
}

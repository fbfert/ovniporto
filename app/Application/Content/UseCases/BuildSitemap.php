<?php

namespace App\Application\Content\UseCases;

use App\Domain\Content\Contracts\PublishedContentIndex;
use App\Domain\Content\Sharing\SitemapEntry;

final readonly class BuildSitemap
{
    /** Fixed public pages. Members' area, panel, checkout and orders never enter the sitemap. */
    public const PAGES = [
        '/', '/lenda', '/faq', '/comunidade', '/o-lugar', '/apoie', '/regiao', '/obra',
        '/mapa', '/loja', '/postal', '/privacidade', '/termos',
    ];

    public function __construct(private PublishedContentIndex $content) {}

    /** @return list<SitemapEntry> */
    public function execute(): array
    {
        return [
            ...array_map(fn (string $path) => new SitemapEntry($path), self::PAGES),
            ...$this->content->entries(),
        ];
    }
}

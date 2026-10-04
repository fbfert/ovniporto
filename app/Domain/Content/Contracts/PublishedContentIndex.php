<?php

namespace App\Domain\Content\Contracts;

use App\Domain\Content\Sharing\SitemapEntry;

/** Every public content page, using the same "published" rule as the page itself. */
interface PublishedContentIndex
{
    /** @return list<SitemapEntry> */
    public function entries(): array;
}

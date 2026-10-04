<?php

namespace App\Domain\Content\Sharing;

use DateTimeInterface;

final readonly class SitemapEntry
{
    public function __construct(
        /** Path from the site root, starting with "/". */
        public string $path,
        public ?DateTimeInterface $lastModified = null,
    ) {}
}

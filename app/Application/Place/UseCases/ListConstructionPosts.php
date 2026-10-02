<?php

namespace App\Application\Place\UseCases;

use App\Domain\Place\Contracts\ConstructionPostRepository;

/** Shared by /obra and /obra.rss, so both always apply the same publication filter. */
final readonly class ListConstructionPosts
{
    public function __construct(private ConstructionPostRepository $posts) {}

    /** @return list<array{slug: string, title: string, excerpt: ?string, cover: ?string, coverAlt: ?string, phase: int, publishedAt: string}> */
    public function execute(): array
    {
        return $this->posts->published();
    }
}

<?php

namespace App\Domain\Place\Contracts;

/** Construction diary. Implementations MUST return only posts whose publication date has passed. */
interface ConstructionPostRepository
{
    /**
     * Newest first.
     *
     * @return list<array{slug: string, title: string, excerpt: ?string, cover: ?string, coverAlt: ?string, phase: int, publishedAt: string}>
     */
    public function published(): array;

    /** @return array{slug: string, title: string, excerpt: ?string, cover: ?string, coverAlt: ?string, phase: int, publishedAt: string, body: string, gallery: list<array{url: string, alt: string}>}|null */
    public function findPublished(string $slug): ?array;
}

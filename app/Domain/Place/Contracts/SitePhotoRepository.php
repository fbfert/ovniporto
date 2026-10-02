<?php

namespace App\Domain\Place\Contracts;

interface SitePhotoRepository
{
    /**
     * Real photos of the land, in display order.
     *
     * @return list<array{url: string, alt: string, caption: ?string, takenAt: ?string}>
     */
    public function all(): array;
}

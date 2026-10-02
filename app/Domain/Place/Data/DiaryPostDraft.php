<?php

namespace App\Domain\Place\Data;

use DateTimeImmutable;
use InvalidArgumentException;

/**
 * A construction diary post as the admin writes it. No publication date keeps
 * it as a draft; a future date schedules it (it stays hidden until then).
 */
final readonly class DiaryPostDraft
{
    public function __construct(
        public string $title,
        public ?string $excerpt,
        public string $body,
        public int $phase,
        public ?DateTimeImmutable $publishedAt,
        public ?string $coverAlt,
    ) {}

    /** Every image on the site has an alt text: a cover without one is refused. */
    public function assertCoverDescribed(bool $hasCover): void
    {
        if ($hasCover && trim((string) $this->coverAlt) === '') {
            throw new InvalidArgumentException('Descreva a capa (texto alternativo).');
        }
    }
}

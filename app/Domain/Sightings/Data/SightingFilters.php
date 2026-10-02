<?php

namespace App\Domain\Sightings\Data;

use App\Domain\Sightings\SightingType;
use DateTimeImmutable;

/** The Livro's filters: a period ending today and, optionally, one type. */
final readonly class SightingFilters
{
    public const PERIODS = ['30d' => '-30 days', '6m' => '-6 months', '1y' => '-1 year', 'all' => null];

    public function __construct(
        public string $period = 'all',
        public ?SightingType $type = null,
    ) {}

    public static function from(?string $period, ?string $type): self
    {
        return new self(
            array_key_exists((string) $period, self::PERIODS) ? (string) $period : 'all',
            SightingType::tryFrom((string) $type),
        );
    }

    /** First observed date included, or null for "Tudo". */
    public function since(DateTimeImmutable $today): ?DateTimeImmutable
    {
        $modifier = self::PERIODS[$this->period] ?? null;

        return $modifier === null ? null : $today->modify($modifier)->setTime(0, 0);
    }

    public function cacheKey(): string
    {
        return $this->period.':'.($this->type === null ? 'all' : $this->type->value);
    }
}

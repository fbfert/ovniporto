<?php

namespace App\Application\Sightings\UseCases;

use App\Domain\Sightings\Contracts\SightingReadRepository;
use App\Domain\Sightings\Data\SightingCard;
use App\Domain\Sightings\Data\SightingFilters;
use App\Infrastructure\Sightings\PublicSightingsCache;

/** The one "public reports" query behind the API, the map and the polaroid list. */
final readonly class ListPublicSightings
{
    public const PER_PAGE = 12;

    public function __construct(private SightingReadRepository $sightings) {}

    /** @return list<array{id: int, type: string, lat: float, lng: float, date: string, nickname: string, thumb: ?string}> */
    public function pins(SightingFilters $filters): array
    {
        return PublicSightingsCache::remember('pins:'.$filters->cacheKey(), fn () => $this->sightings->pins($filters));
    }

    public function total(SightingFilters $filters): int
    {
        return PublicSightingsCache::remember('total:'.$filters->cacheKey(), fn () => $this->sightings->countPublic($filters));
    }

    /** @return array{cards: list<array<string, mixed>>, hasMore: bool} */
    public function page(SightingFilters $filters, int $page): array
    {
        $cards = $this->sightings->page($filters, $page, self::PER_PAGE);

        return [
            'cards' => array_map(fn (SightingCard $card) => $card->toArray(), $cards),
            'hasMore' => $page * self::PER_PAGE < $this->total($filters),
        ];
    }
}

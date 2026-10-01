<?php

namespace App\Application\Content\Data;

use App\Domain\Catalog\Data\ProductCard;
use App\Domain\Place\Data\PlaceSpaceCard;
use App\Domain\Region\Data\PartnerCard;
use App\Domain\Sightings\Data\SightingCard;

final readonly class HomeData
{
    /**
     * @param  array<string, string|null>  $content
     * @param  list<SightingCard>  $sightings
     * @param  list<ProductCard>  $products
     * @param  list<PlaceSpaceCard>  $spaces
     * @param  list<PartnerCard>  $partners
     */
    public function __construct(
        public int $membersCount,
        public int $sightingsCount,
        public array $content,
        public array $sightings,
        public array $products,
        public array $spaces,
        public array $partners,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'counters' => [
                'members' => $this->membersCount,
                'sightings' => $this->sightingsCount,
            ],
            'content' => $this->content,
            'sightings' => array_map(fn (SightingCard $s) => $s->toArray(), $this->sightings),
            'products' => array_map(fn (ProductCard $p) => $p->toArray(), $this->products),
            'spaces' => array_map(fn (PlaceSpaceCard $s) => $s->toArray(), $this->spaces),
            'partners' => array_map(fn (PartnerCard $p) => $p->toArray(), $this->partners),
        ];
    }
}

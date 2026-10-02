<?php

namespace App\Application\Region\UseCases;

use App\Domain\Region\Contracts\RegionPartnerRepository;
use App\Domain\Region\Distance;
use App\Domain\Region\PartnerType;

final readonly class ListRegionPartners
{
    public function __construct(private RegionPartnerRepository $partners) {}

    /**
     * `total` counts every published partner, so the page tells "no results" apart from "list still empty".
     *
     * @return array{partners: list<array<string, mixed>>, total: int, origin: array{lat: float, lng: float}}
     */
    public function execute(?PartnerType $type = null, ?string $search = null): array
    {
        $origin = self::origin();

        return [
            'partners' => array_map(fn (array $partner) => [
                ...$partner,
                'distanceKm' => $partner['lat'] === null || $partner['lng'] === null
                    ? null
                    : Distance::km($origin['lat'], $origin['lng'], $partner['lat'], $partner['lng']),
            ], $this->partners->published($type, $search)),
            'total' => $this->partners->countPublished(),
            'origin' => $origin,
        ];
    }

    /** @return array{lat: float, lng: float} */
    public static function origin(): array
    {
        return [
            'lat' => (float) config('ovniporto.location.lat'),
            'lng' => (float) config('ovniporto.location.lng'),
        ];
    }
}

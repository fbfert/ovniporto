<?php

namespace App\Application\Region\UseCases;

use App\Domain\Region\Contracts\RegionPartnerRepository;
use App\Domain\Region\Distance;

final readonly class GetRegionPartner
{
    public function __construct(private RegionPartnerRepository $partners) {}

    /** @return array{partner: array<string, mixed>, origin: array{lat: float, lng: float}}|null null unless published with consent */
    public function execute(string $slug): ?array
    {
        $partner = $this->partners->findPublished($slug);
        if ($partner === null) {
            return null;
        }

        $origin = ListRegionPartners::origin();
        $partner['distanceKm'] = $partner['lat'] === null || $partner['lng'] === null
            ? null
            : Distance::km($origin['lat'], $origin['lng'], $partner['lat'], $partner['lng']);

        return ['partner' => $partner, 'origin' => $origin];
    }
}

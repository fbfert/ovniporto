<?php

namespace App\Infrastructure\Geo;

use App\Domain\Map\Contracts\Geocoder;

/**
 * No network: every report is in "Lages, SC" and addresses fall on the runway.
 * For the end-to-end suite and offline development (GEOCODER=offline), so they
 * never depend on Nominatim or its one-request-per-second limit.
 */
final class OfflineGeocoder implements Geocoder
{
    public function cityAt(float $lat, float $lng): string
    {
        return 'Lages, SC';
    }

    /** @return array{lat: float, lng: float} */
    public function locate(string $address): array
    {
        return ['lat' => (float) config('ovniporto.location.lat'), 'lng' => (float) config('ovniporto.location.lng')];
    }
}

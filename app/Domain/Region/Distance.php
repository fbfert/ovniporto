<?php

namespace App\Domain\Region;

/** Straight-line distance on the Earth's surface (Haversine), rounded to whole km. */
final class Distance
{
    private const EARTH_RADIUS_KM = 6371.0;

    public static function km(float $fromLat, float $fromLng, float $toLat, float $toLng): int
    {
        $dLat = deg2rad($toLat - $fromLat);
        $dLng = deg2rad($toLng - $fromLng);
        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($fromLat)) * cos(deg2rad($toLat)) * sin($dLng / 2) ** 2;

        return (int) round(self::EARTH_RADIUS_KM * 2 * atan2(sqrt($a), sqrt(1 - $a)));
    }
}

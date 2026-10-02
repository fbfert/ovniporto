<?php

namespace App\Domain\Map\Contracts;

/** Points and places: the town around a report (moderators only), a partner's address (panel). */
interface Geocoder
{
    /** "Lages, SC", or null when the service has no answer. */
    public function cityAt(float $lat, float $lng): ?string;

    /** @return array{lat: float, lng: float}|null a first guess the operator adjusts on the map */
    public function locate(string $address): ?array;
}

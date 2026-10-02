<?php

namespace App\Domain\Map\Contracts;

/** Turns a point into the name of the town around it, for the moderators' eyes only. */
interface Geocoder
{
    /** "Lages, SC", or null when the service has no answer. */
    public function cityAt(float $lat, float $lng): ?string;
}

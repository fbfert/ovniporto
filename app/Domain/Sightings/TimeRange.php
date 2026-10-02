<?php

namespace App\Domain\Sightings;

/** When the exact time isn't known: the stretch of the night. */
enum TimeRange: string
{
    case Dusk = 'dusk';
    case Night = 'night';
    case Dawn = 'dawn';
}

<?php

namespace App\Domain\Sightings;

enum SightingType: string
{
    case Light = 'light';
    case Object = 'object';
    case Trail = 'trail';
    case Other = 'other';
}

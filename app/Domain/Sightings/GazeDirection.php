<?php

namespace App\Domain\Sightings;

/** Where the person was looking, in Portuguese compass points (L = leste, O = oeste). */
enum GazeDirection: string
{
    case N = 'N';
    case NE = 'NE';
    case L = 'L';
    case SE = 'SE';
    case S = 'S';
    case SO = 'SO';
    case O = 'O';
    case NO = 'NO';
}

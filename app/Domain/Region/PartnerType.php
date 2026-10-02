<?php

namespace App\Domain\Region;

enum PartnerType: string
{
    case Inn = 'inn';
    case Attraction = 'attraction';
    case Producer = 'producer';
    case Restaurant = 'restaurant';
    case Other = 'other';
}

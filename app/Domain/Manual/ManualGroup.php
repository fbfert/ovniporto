<?php

namespace App\Domain\Manual;

/** The branches of the manual's overview map; chapters are listed group by group, in this order. */
enum ManualGroup: string
{
    case Start = 'start';
    case Community = 'community';
    case Store = 'store';
    case Site = 'site';
    case Backstage = 'backstage';
    case Reference = 'reference';
}

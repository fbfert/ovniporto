<?php

namespace App\Domain\Campaign;

enum CampaignStatus: string
{
    case Planning = 'planning';
    case Open = 'open';
    case Closed = 'closed';
}

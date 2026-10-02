<?php

namespace App\Domain\Campaign\Contracts;

use App\Domain\Campaign\Data\CampaignSettings;

interface CampaignRepository
{
    public function settings(): CampaignSettings;

    /** @return list<string> names of supporters who consented to have them published */
    public function publishableSupporterNames(): array;

    /** @return list<array{name: string, tier: ?string, url: ?string, logo: ?string}> */
    public function sponsors(): array;
}

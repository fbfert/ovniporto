<?php

namespace App\Domain\Region\Contracts;

use App\Domain\Region\Data\PartnerCard;

interface RegionPartnerRepository
{
    /**
     * Featured partners that are publishable (see PartnerPublication).
     *
     * @return list<PartnerCard>
     */
    public function featuredPublished(int $limit): array;
}

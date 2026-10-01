<?php

namespace App\Application\Content\UseCases;

use App\Application\Content\Data\HomeData;
use App\Domain\Catalog\Contracts\ProductReadRepository;
use App\Domain\Members\Contracts\MemberRepository;
use App\Domain\Place\Contracts\PlaceSpaceRepository;
use App\Domain\Region\Contracts\RegionPartnerRepository;
use App\Domain\Sightings\Contracts\SightingReadRepository;

final readonly class GetHomeData
{
    public const SIGHTINGS_ON_HOME = 4;

    public const PRODUCTS_ON_HOME = 3;

    public const PARTNERS_ON_HOME = 3;

    public function __construct(
        private GetHomeContent $content,
        private MemberRepository $members,
        private SightingReadRepository $sightings,
        private ProductReadRepository $products,
        private PlaceSpaceRepository $spaces,
        private RegionPartnerRepository $partners,
    ) {}

    public function execute(): HomeData
    {
        return new HomeData(
            membersCount: $this->members->countActive(),
            sightingsCount: $this->sightings->countApproved(),
            content: $this->content->execute(),
            sightings: $this->sightings->latestApproved(self::SIGHTINGS_ON_HOME),
            products: $this->products->featured(self::PRODUCTS_ON_HOME),
            spaces: $this->spaces->all(),
            partners: $this->partners->featuredPublished(self::PARTNERS_ON_HOME),
        );
    }
}

<?php

namespace App\Application\Region\UseCases;

use App\Domain\Region\Contracts\RegionPartnerRepository;
use App\Domain\Region\PartnerPublication;
use DateTimeInterface;
use InvalidArgumentException;

/** Used by the panel (add-operations-panel): publishing without recorded consent is refused by the domain. */
final readonly class PublishRegionPartner
{
    public function __construct(private RegionPartnerRepository $partners) {}

    public function execute(string $slug, DateTimeInterface $at): void
    {
        $partner = $this->partners->findForPublication($slug)
            ?? throw new InvalidArgumentException("Unknown partner: {$slug}");

        PartnerPublication::assertCanBePublished($slug, $partner['consentGivenAt']);
        $this->partners->markPublished($slug, $at);
    }
}

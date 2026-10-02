<?php

namespace App\Domain\Region;

use DomainException;

final class PartnerWithoutConsent extends DomainException
{
    public static function forPartner(string $slug): self
    {
        return new self("Partner [{$slug}] cannot be published without recorded consent.");
    }
}

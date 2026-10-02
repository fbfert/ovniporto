<?php

namespace App\Domain\Region\Data;

use App\Domain\Region\PartnerType;
use DateTimeImmutable;

/** A region partner as the panel form sends it. */
final readonly class PartnerFields
{
    public function __construct(
        public string $name,
        public PartnerType $type,
        public ?string $shortDescription,
        public string $city,
        public ?string $address,
        public ?float $lat,
        public ?float $lng,
        public ?string $phone,
        public ?string $whatsapp,
        public ?string $instagram,
        public ?string $website,
        public bool $isFeatured,
        public ?DateTimeImmutable $consentGivenAt,
    ) {}
}

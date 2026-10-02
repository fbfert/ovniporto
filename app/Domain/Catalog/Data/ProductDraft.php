<?php

namespace App\Domain\Catalog\Data;

/** A product as the panel form sends it; money already in cents. */
final readonly class ProductDraft
{
    /** @param array{length: int, width: int, height: int}|null $dimensions cm */
    public function __construct(
        public string $name,
        public ?string $shortDescription,
        public ?string $description,
        public int $priceCents,
        public ?int $comparePriceCents,
        public bool $madeToOrder,
        public int $productionDays,
        public int $weightGrams,
        public ?array $dimensions,
        public ?string $label,
        public bool $active,
        public bool $featured,
    ) {}
}

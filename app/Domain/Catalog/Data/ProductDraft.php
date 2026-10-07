<?php

namespace App\Domain\Catalog\Data;

/** A product as the panel form sends it; money already in cents. */
final readonly class ProductDraft
{
    /** @param array{length: float, width: float, height: float}|null $dimensions cm, down to 0.01 (a tenth of a millimetre) */
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

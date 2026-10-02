<?php

namespace App\Domain\Catalog\Data;

use App\Domain\Catalog\Money;

/** A variant as the cart sees it: the current price (from the database) and what limits buying it. */
final readonly class PurchasableVariant
{
    public function __construct(
        public int $variantId,
        public string $productName,
        public string $productSlug,
        public string $variantName,
        public Money $unitPrice,
        public bool $madeToOrder,
        public int $productionDays,
        public ?int $stock,
        public bool $available,
        public int $weightGrams,
        public ?string $imageUrl,
        public ?string $imageAlt,
    ) {}
}

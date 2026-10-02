<?php

namespace App\Domain\Catalog\Contracts;

use App\Domain\Catalog\Data\ProductCard;
use App\Domain\Catalog\Data\PurchasableVariant;

/** The public catalog. Inactive products never come out of here. */
interface ProductReadRepository
{
    /** @return list<ProductCard> Active, featured products only. */
    public function featured(int $limit): array;

    /** @return list<ProductCard> every active product, in the store's order */
    public function active(): array;

    /**
     * An active product with its images and variants (each with price and limit inputs).
     *
     * @return array<string, mixed>|null
     */
    public function findActive(string $slug): ?array;

    /** @return list<ProductCard> other active products, for "Combina com" */
    public function related(string $exceptSlug, int $limit): array;

    /** Any variant, active or not: the cart decides what to do with an unavailable one. */
    public function variant(int $variantId): ?PurchasableVariant;

    /**
     * @param  list<int>  $variantIds
     * @return array<int, PurchasableVariant> by variant id
     */
    public function variants(array $variantIds): array;
}

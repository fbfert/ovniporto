<?php

namespace App\Domain\Catalog\Contracts;

use App\Domain\Catalog\Data\ProductCard;

interface ProductReadRepository
{
    /** @return list<ProductCard> Active, featured products only. */
    public function featured(int $limit): array;
}

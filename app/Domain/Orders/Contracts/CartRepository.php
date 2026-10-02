<?php

namespace App\Domain\Orders\Contracts;

use App\Domain\Orders\Data\CartOwner;

/** Only variant ids and quantities are stored: prices are always read fresh from the catalog. */
interface CartRepository
{
    /** @return array<int, int> quantity by variant id, oldest first */
    public function quantities(CartOwner $owner): array;

    /** 0 removes the item. */
    public function setQuantity(CartOwner $owner, int $variantId, int $quantity): void;

    /** Moves the visitor's items into the member's cart (summing quantities) and deletes the visitor cart. */
    public function merge(string $visitorToken, int $memberId): void;
}

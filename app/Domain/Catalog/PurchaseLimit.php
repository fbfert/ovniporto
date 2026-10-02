<?php

namespace App\Domain\Catalog;

use App\Domain\Catalog\Data\PurchasableVariant;

/**
 * How many units of a variant one cart may hold. In-stock items never go past
 * the stock (it is only decremented after payment, so checkout checks again);
 * made-to-order items have a sane cap per order; inactive ones, zero.
 */
final class PurchaseLimit
{
    public const MADE_TO_ORDER_MAX = 20;

    public static function of(PurchasableVariant $variant): int
    {
        if (! $variant->available) {
            return 0;
        }

        return $variant->madeToOrder ? self::MADE_TO_ORDER_MAX : max(0, $variant->stock ?? 0);
    }
}

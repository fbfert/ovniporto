<?php

namespace App\Domain\Orders;

use App\Domain\Catalog\Money;

/** Items at today's prices plus the chosen freight, in cents. The only amount the payment provider sees. */
final readonly class OrderTotals
{
    private function __construct(
        public Money $subtotal,
        public Money $shipping,
        public Money $total,
    ) {}

    /** @param list<array{unitPrice: Money, quantity: int}> $lines */
    public static function of(array $lines, Money $shipping): self
    {
        $subtotal = Money::zero();
        foreach ($lines as $line) {
            $subtotal = $subtotal->plus($line['unitPrice']->times($line['quantity']));
        }

        return new self($subtotal, $shipping, $subtotal->plus($shipping));
    }
}

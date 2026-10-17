<?php

namespace App\Domain\Orders;

use App\Domain\Catalog\Money;

/**
 * What the buyer pays of a freight quoted for the whole cart: the share of the weight of the items
 * without included shipping, rounded up to the cent. The OVNIPORTO pays the rest.
 */
final readonly class ChargedShipping
{
    private function __construct(
        private int $totalGrams,
        private int $payableGrams,
        private bool $hasPayableItems,
    ) {}

    /** The cart already summed the weights (ManageCart::view). */
    public static function forWeights(int $totalGrams, int $payableGrams, bool $hasPayableItems): self
    {
        return new self($totalGrams, $payableGrams, $hasPayableItems);
    }

    /** @param list<array{weightGrams: int, quantity: int, shippingIncluded: bool}> $lines */
    public static function forLines(array $lines): self
    {
        $total = 0;
        $payable = 0;
        $hasPayable = false;
        foreach ($lines as $line) {
            $grams = $line['weightGrams'] * $line['quantity'];
            $total += $grams;
            if (! $line['shippingIncluded']) {
                $payable += $grams;
                $hasPayable = true;
            }
        }

        return new self($total, $payable, $hasPayable);
    }

    public function of(Money $quoted): Money
    {
        if (! $this->hasPayableItems) {
            return Money::zero();
        }
        if ($this->totalGrams === 0) {
            return $quoted;
        }

        return Money::cents(intdiv($quoted->cents * $this->payableGrams + $this->totalGrams - 1, $this->totalGrams));
    }

    /** Some item ships at the OVNIPORTO's cost, so the buyer may pay less than the quote. */
    public function includesAny(): bool
    {
        return $this->payableGrams < $this->totalGrams || ! $this->hasPayableItems;
    }
}

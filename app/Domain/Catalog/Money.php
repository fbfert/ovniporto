<?php

namespace App\Domain\Catalog;

use InvalidArgumentException;

/** Brazilian reais in cents. Never a float: every sum in the store goes through here. */
final readonly class Money
{
    private function __construct(public int $cents) {}

    public static function cents(int $cents): self
    {
        if ($cents < 0) {
            throw new InvalidArgumentException('Money cannot be negative.');
        }

        return new self($cents);
    }

    public static function zero(): self
    {
        return new self(0);
    }

    public function plus(self $other): self
    {
        return new self($this->cents + $other->cents);
    }

    /** A variant's price: the product price plus its (possibly negative) delta, never below zero. */
    public function adjustedBy(int $deltaCents): self
    {
        return new self(max(0, $this->cents + $deltaCents));
    }

    public function times(int $quantity): self
    {
        if ($quantity < 0) {
            throw new InvalidArgumentException('Quantity cannot be negative.');
        }

        return new self($this->cents * $quantity);
    }

    /** "8.00" for schema.org and payment providers. */
    public function decimal(): string
    {
        return sprintf('%d.%02d', intdiv($this->cents, 100), $this->cents % 100);
    }

    public function equals(self $other): bool
    {
        return $this->cents === $other->cents;
    }
}

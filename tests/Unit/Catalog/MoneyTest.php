<?php

use App\Domain\Catalog\Money;

it('adds and multiplies in cents, without floats', function () {
    $sticker = Money::cents(800);

    expect($sticker->times(3)->plus(Money::cents(1990))->cents)->toBe(4390)
        ->and(Money::zero()->cents)->toBe(0);
});

it('applies a variant delta and never goes below zero', function () {
    expect(Money::cents(7900)->adjustedBy(1000)->cents)->toBe(8900)
        ->and(Money::cents(500)->adjustedBy(-900)->cents)->toBe(0);
});

it('prints the decimal form for structured data', function () {
    expect(Money::cents(800)->decimal())->toBe('8.00')
        ->and(Money::cents(18905)->decimal())->toBe('189.05')
        ->and(Money::cents(7)->decimal())->toBe('0.07');
});

it('refuses negative amounts and quantities', function () {
    expect(fn () => Money::cents(-1))->toThrow(InvalidArgumentException::class)
        ->and(fn () => Money::cents(100)->times(-2))->toThrow(InvalidArgumentException::class);
});

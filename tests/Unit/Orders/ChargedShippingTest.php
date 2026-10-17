<?php

use App\Domain\Catalog\Money;
use App\Domain\Orders\ChargedShipping;

function line(int $grams, bool $included, int $quantity = 1): array
{
    return ['weightGrams' => $grams, 'quantity' => $quantity, 'shippingIncluded' => $included];
}

it('charges the whole quote when no item has included shipping', function () {
    $charged = ChargedShipping::forLines([line(100, false, 2)]);

    expect($charged->of(Money::cents(2000))->cents)->toBe(2000)
        ->and($charged->includesAny())->toBeFalse();
});

it('charges nothing when every item has included shipping', function () {
    $charged = ChargedShipping::forLines([line(300, true), line(0, true)]);

    expect($charged->of(Money::cents(2000))->cents)->toBe(0)
        ->and($charged->includesAny())->toBeTrue();
});

it('charges the weight share of the items without included shipping', function () {
    $charged = ChargedShipping::forLines([line(300, true), line(100, false)]);

    expect($charged->of(Money::cents(2000))->cents)->toBe(500)
        ->and($charged->includesAny())->toBeTrue();
});

it('rounds the share up to the cent', function () {
    expect(ChargedShipping::forLines([line(200, true), line(100, false)])->of(Money::cents(1000))->cents)->toBe(334);
});

it('charges the whole quote when no item has a weight and some item pays freight', function () {
    expect(ChargedShipping::forLines([line(0, true), line(0, false)])->of(Money::cents(1590))->cents)->toBe(1590);
});

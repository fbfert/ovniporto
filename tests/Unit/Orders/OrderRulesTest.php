<?php

use App\Domain\Catalog\Money;
use App\Domain\Orders\Cpf;
use App\Domain\Orders\InvalidOrderTransition;
use App\Domain\Orders\OrderNumber;
use App\Domain\Orders\OrderStatus;
use App\Domain\Orders\OrderTotals;
use App\Domain\Orders\Phone;

it('adds items and freight in cents', function () {
    $totals = OrderTotals::of([['unitPrice' => Money::cents(800), 'quantity' => 2]], Money::cents(1250));

    expect($totals->subtotal->cents)->toBe(1600)
        ->and($totals->shipping->cents)->toBe(1250)
        ->and($totals->total->cents)->toBe(2850);
});

it('formats the order number', function () {
    expect(OrderNumber::format(2026, 123))->toBe('OVP-2026-000123')
        ->and(OrderNumber::isValid('OVP-2026-000123'))->toBeTrue()
        ->and(OrderNumber::isValid('OVP-26-123'))->toBeFalse();
    expect(fn () => OrderNumber::format(2026, 0))->toThrow(InvalidArgumentException::class);
});

it('follows the status order and refuses skipping a step', function () {
    expect(OrderStatus::PendingPayment->canMoveTo(OrderStatus::Paid))->toBeTrue()
        ->and(OrderStatus::Paid->canMoveTo(OrderStatus::InProduction))->toBeTrue()
        ->and(OrderStatus::Shipped->canMoveTo(OrderStatus::Delivered))->toBeTrue()
        ->and(OrderStatus::Canceled->next())->toBe([]);
    expect(fn () => OrderStatus::PendingPayment->assertCanMoveTo(OrderStatus::Shipped))->toThrow(InvalidOrderTransition::class);
    expect(fn () => OrderStatus::PendingPayment->assertCanMoveTo(OrderStatus::Refunded))->toThrow(InvalidOrderTransition::class);
});

it('allows a shipping label only for paid orders not yet shipped', function () {
    expect(OrderStatus::Paid->allowsShippingLabel())->toBeTrue()
        ->and(OrderStatus::InProduction->allowsShippingLabel())->toBeTrue()
        ->and(OrderStatus::PendingPayment->allowsShippingLabel())->toBeFalse()
        ->and(OrderStatus::Shipped->allowsShippingLabel())->toBeFalse();
});

it('validates CPF check digits and masks it', function () {
    expect(Cpf::from('529.982.247-25')->digits)->toBe('52998224725')
        ->and(Cpf::mask('52998224725'))->toBe('***.982.247-**');
    foreach (['529.982.247-24', '111.111.111-11', '123', ''] as $bad) {
        expect(fn () => Cpf::from($bad))->toThrow(InvalidArgumentException::class);
    }
});

it('accepts Brazilian phones with area code', function () {
    expect(Phone::from('(49) 99999-0000')->digits)->toBe('49999990000')
        ->and(Phone::from('+55 49 3222-1111')->digits)->toBe('4932221111');
    expect(fn () => Phone::from('99999-0000'))->toThrow(InvalidArgumentException::class);
});

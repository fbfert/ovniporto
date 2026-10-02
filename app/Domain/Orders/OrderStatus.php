<?php

namespace App\Domain\Orders;

/**
 * pending_payment → paid → in_production → shipped → delivered,
 * with canceled (before payment) and refunded (after) as the ways out.
 */
enum OrderStatus: string
{
    case PendingPayment = 'pending_payment';
    case Paid = 'paid';
    case InProduction = 'in_production';
    case Shipped = 'shipped';
    case Delivered = 'delivered';
    case Canceled = 'canceled';
    case Refunded = 'refunded';

    /** @return list<self> */
    public function next(): array
    {
        return match ($this) {
            self::PendingPayment => [self::Paid, self::Canceled],
            self::Paid => [self::InProduction, self::Refunded],
            self::InProduction => [self::Shipped, self::Refunded],
            self::Shipped => [self::Delivered, self::Refunded],
            self::Delivered => [self::Refunded],
            self::Canceled, self::Refunded => [],
        };
    }

    public function canMoveTo(self $to): bool
    {
        return in_array($to, $this->next(), true);
    }

    public function assertCanMoveTo(self $to): void
    {
        if (! $this->canMoveTo($to)) {
            throw new InvalidOrderTransition($this, $to);
        }
    }

    /** A label can only be bought for an order that was paid and is not out the door yet. */
    public function allowsShippingLabel(): bool
    {
        return in_array($this, [self::Paid, self::InProduction], true);
    }
}

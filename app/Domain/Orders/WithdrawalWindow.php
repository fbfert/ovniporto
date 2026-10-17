<?php

namespace App\Domain\Orders;

use DateTimeImmutable;
use DateTimeInterface;

/**
 * The 7 calendar days of the withdrawal right (CDC art. 49), counted from the day the order was
 * marked delivered. An order not delivered yet is always within the window, in the buyer's favour.
 */
final class WithdrawalWindow
{
    public const DAYS = 7;

    public static function covers(?DateTimeInterface $deliveredAt, DateTimeInterface $requestedAt): bool
    {
        if ($deliveredAt === null) {
            return true;
        }
        $deadline = DateTimeImmutable::createFromInterface($deliveredAt)->modify('+'.self::DAYS.' days');

        return $requestedAt <= $deadline;
    }
}

<?php

namespace App\Domain\Orders;

use InvalidArgumentException;

/** "OVP-2026-000123": the year and that year's sequence, six digits. */
final class OrderNumber
{
    public const PATTERN = '/^OVP-\d{4}-\d{6}$/';

    public static function format(int $year, int $sequence): string
    {
        if ($sequence < 1 || $sequence > 999_999) {
            throw new InvalidArgumentException('Order sequence out of range.');
        }

        return sprintf('OVP-%04d-%06d', $year, $sequence);
    }

    public static function isValid(string $number): bool
    {
        return preg_match(self::PATTERN, $number) === 1;
    }
}

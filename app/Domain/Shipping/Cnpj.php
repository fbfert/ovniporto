<?php

namespace App\Domain\Shipping;

/** A CNPJ with valid check digits: the sender of the labels may be a company. */
final class Cnpj
{
    private const WEIGHTS = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];

    public static function isValid(string $digits): bool
    {
        if (strlen($digits) !== 14 || preg_match('/^(\d)\1{13}$/', $digits) === 1) {
            return false;
        }
        foreach ([12, 13] as $length) {
            $weights = $length === 12 ? self::WEIGHTS : [6, ...self::WEIGHTS];
            $sum = 0;
            for ($i = 0; $i < $length; $i++) {
                $sum += (int) $digits[$i] * $weights[$i];
            }
            $rest = $sum % 11;
            if (($rest < 2 ? 0 : 11 - $rest) !== (int) $digits[$length]) {
                return false;
            }
        }

        return true;
    }
}

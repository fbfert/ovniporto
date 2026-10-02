<?php

namespace App\Domain\Orders;

use InvalidArgumentException;

/** A CPF with valid check digits. Shown masked everywhere outside the panel: ***.456.789-** */
final readonly class Cpf
{
    private function __construct(public string $digits) {}

    public static function from(string $value): self
    {
        $digits = preg_replace('/\D/', '', $value) ?? '';
        if (! self::isValid($digits)) {
            throw new InvalidArgumentException('CPF inválido. Confira os números.');
        }

        return new self($digits);
    }

    public static function isValid(string $digits): bool
    {
        if (strlen($digits) !== 11 || preg_match('/^(\d)\1{10}$/', $digits) === 1) {
            return false;
        }
        foreach ([9, 10] as $length) {
            $sum = 0;
            for ($i = 0; $i < $length; $i++) {
                $sum += (int) $digits[$i] * ($length + 1 - $i);
            }
            $check = ($sum * 10) % 11 % 10;
            if ($check !== (int) $digits[$length]) {
                return false;
            }
        }

        return true;
    }

    public static function mask(string $digits): string
    {
        return strlen($digits) === 11 ? '***.'.substr($digits, 3, 3).'.'.substr($digits, 6, 3).'-**' : '***';
    }
}

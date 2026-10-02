<?php

namespace App\Domain\Orders;

use InvalidArgumentException;

/** A Brazilian phone with area code (WhatsApp): 10 or 11 digits, "+55" accepted. */
final readonly class Phone
{
    private function __construct(public string $digits) {}

    public static function from(string $value): self
    {
        $digits = preg_replace('/\D/', '', $value) ?? '';
        if (strlen($digits) > 11 && str_starts_with($digits, '55')) {
            $digits = substr($digits, 2);
        }
        if (! preg_match('/^[1-9]{2}9?\d{8}$/', $digits)) {
            throw new InvalidArgumentException('Telefone com DDD, só números. Ex.: (49) 99999-0000.');
        }

        return new self($digits);
    }
}

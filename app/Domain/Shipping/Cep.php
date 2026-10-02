<?php

namespace App\Domain\Shipping;

use InvalidArgumentException;

/** A Brazilian postal code: exactly 8 digits ("88500-000" or "88500000"). */
final readonly class Cep
{
    private function __construct(public string $digits) {}

    public static function from(string $value): self
    {
        $digits = preg_replace('/\D/', '', $value) ?? '';
        if (strlen($digits) !== 8 || $digits === '00000000') {
            throw new InvalidArgumentException('Digite um CEP com 8 números.');
        }

        return new self($digits);
    }

    public function formatted(): string
    {
        return substr($this->digits, 0, 5).'-'.substr($this->digits, 5);
    }
}

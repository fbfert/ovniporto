<?php

namespace App\Domain\Orders\Data;

use InvalidArgumentException;

/** Whose cart: a signed-in member, or a visitor identified by a random token kept in the session. */
final readonly class CartOwner
{
    private function __construct(
        public ?int $memberId,
        public ?string $token,
    ) {}

    public static function member(int $memberId): self
    {
        return new self($memberId, null);
    }

    public static function visitor(string $token): self
    {
        if ($token === '') {
            throw new InvalidArgumentException('A visitor cart needs a token.');
        }

        return new self(null, $token);
    }
}

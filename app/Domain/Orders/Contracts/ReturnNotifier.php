<?php

namespace App\Domain\Orders\Contracts;

/**
 * A request was stored: the buyer gets the protocol and the answer deadline, the team gets only the
 * protocol and the panel link.
 */
interface ReturnNotifier
{
    public function received(string $protocol, string $buyerEmail): void;
}

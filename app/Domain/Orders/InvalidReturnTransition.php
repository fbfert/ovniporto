<?php

namespace App\Domain\Orders;

use DomainException;

final class InvalidReturnTransition extends DomainException
{
    public function __construct(public readonly ReturnRequestStatus $from, public readonly ReturnRequestStatus $to)
    {
        parent::__construct("Um pedido de devolução \"{$from->value}\" não pode passar para \"{$to->value}\".");
    }
}

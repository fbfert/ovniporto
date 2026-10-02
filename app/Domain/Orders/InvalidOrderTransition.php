<?php

namespace App\Domain\Orders;

use DomainException;

final class InvalidOrderTransition extends DomainException
{
    public function __construct(public readonly OrderStatus $from, public readonly OrderStatus $to)
    {
        parent::__construct("Um pedido \"{$from->value}\" não pode passar para \"{$to->value}\".");
    }
}

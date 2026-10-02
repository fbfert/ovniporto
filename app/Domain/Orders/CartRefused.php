<?php

namespace App\Domain\Orders;

use DomainException;

/** The cart cannot take that quantity; carries the most it can take (0 when the item is unavailable). */
final class CartRefused extends DomainException
{
    public function __construct(public readonly int $max)
    {
        parent::__construct($max === 0
            ? 'Esse item não está disponível agora.'
            : "Temos só {$max} disponíveis. Ajuste a quantidade.");
    }
}

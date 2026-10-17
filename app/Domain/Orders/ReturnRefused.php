<?php

namespace App\Domain\Orders;

use DomainException;

/** Always the same words, whatever did not match: the form never tells which field was wrong. */
final class ReturnRefused extends DomainException
{
    public function __construct()
    {
        parent::__construct('Não encontramos um pedido com esse número e esse e-mail.');
    }
}

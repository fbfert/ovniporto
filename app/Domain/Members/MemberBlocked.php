<?php

namespace App\Domain\Members;

use DomainException;

/** A blocked member tried to send a report (or, with the store, an order). */
final class MemberBlocked extends DomainException
{
    public function __construct()
    {
        parent::__construct('Sua conta está bloqueada pela torre. Escreva para o contato do site se achar que é engano.');
    }
}

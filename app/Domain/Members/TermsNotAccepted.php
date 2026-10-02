<?php

namespace App\Domain\Members;

use DomainException;

final class TermsNotAccepted extends DomainException
{
    public function __construct()
    {
        parent::__construct('The terms must be accepted to activate the account.');
    }
}

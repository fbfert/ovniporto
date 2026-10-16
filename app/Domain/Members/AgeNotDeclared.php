<?php

namespace App\Domain\Members;

use DomainException;

final class AgeNotDeclared extends DomainException
{
    public function __construct()
    {
        parent::__construct('The member must declare being at least 16 years old.');
    }
}

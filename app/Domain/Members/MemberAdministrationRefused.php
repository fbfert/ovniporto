<?php

namespace App\Domain\Members;

use DomainException;

/** A panel action on a member that the rules do not allow. */
final class MemberAdministrationRefused extends DomainException
{
    public function __construct(public readonly string $field, string $message)
    {
        parent::__construct($message);
    }
}

<?php

namespace App\Domain\Members;

use DomainException;

final class NicknameUnavailable extends DomainException
{
    /** @param 'format'|'reserved'|'taken' $reason */
    public function __construct(public readonly string $reason)
    {
        parent::__construct("Nickname unavailable: {$reason}");
    }
}

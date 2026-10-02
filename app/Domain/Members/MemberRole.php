<?php

namespace App\Domain\Members;

enum MemberRole: string
{
    case Member = 'member';
    case Moderator = 'moderator';
    case Store = 'store';
    case Admin = 'admin';

    /** Roles that see the operations panel (add-operations-panel). */
    public function canOpenPanel(): bool
    {
        return $this !== self::Member;
    }
}

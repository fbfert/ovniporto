<?php

namespace App\Domain\Members\Contracts;

use DateTimeInterface;

/** E-mails of the inactivity cycle: the notice to the member and the alert about staff accounts. */
interface InactivityNotifier
{
    /** Tells the member the account will be anonymized on $anonymizeOn unless they sign in. */
    public function noticeAnonymization(string $email, ?string $nickname, DateTimeInterface $anonymizeOn): void;

    /** Tells the admins a panel account reached the term; it is never anonymized automatically. */
    public function alertInactiveStaff(int $memberId, ?string $nickname, string $role, string $lastSeenAt): void;
}

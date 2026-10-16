<?php

namespace App\Domain\Members\Contracts;

/**
 * Port other modules implement for an account anonymized after years without access: like
 * MemberContentEraser, but what was published stays with the nickname. Sightings deletes the
 * unpublished reports, Privacy anonymizes the consents, Campaign and Origin drop the e-mail lists.
 * Orders is not here: the account's orders follow the general tax retention rule.
 */
interface MemberContentAnonymizer
{
    public function anonymizeFor(int $memberId, string $email): void;
}

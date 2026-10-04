<?php

namespace App\Domain\Members\Contracts;

/**
 * Port other modules implement so deleting an account stays decoupled from them:
 * Sightings deletes reports and photos, Orders anonymizes what tax law keeps,
 * Privacy anonymizes the consents, Campaign drops the newsletter sign-up.
 * The e-mail reaches what was given by e-mail before (or without) signing in.
 */
interface MemberContentEraser
{
    public function eraseFor(int $memberId, string $email): void;
}

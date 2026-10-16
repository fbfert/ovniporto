<?php

namespace App\Domain\Members\Contracts;

use App\Domain\Members\Data\Identity;

/** Verifies a Google ID token sent by the Android app's native sign-in. Swappable and faked in tests. */
interface IdTokenVerifier
{
    /**
     * The identity carried by a valid token.
     *
     * @throws \RuntimeException when the token is invalid, expired or meant for another audience
     */
    public function verify(string $idToken): Identity;
}

<?php

namespace App\Domain\Members\Contracts;

use App\Domain\Members\Data\Identity;

/** Sign-in provider (Google). Swappable and faked in tests. */
interface IdentityProvider
{
    /** URL that starts the provider's consent screen (scopes: openid, email, profile only). */
    public function redirectUrl(): string;

    /** The identity returned to the callback. */
    public function identity(): Identity;
}

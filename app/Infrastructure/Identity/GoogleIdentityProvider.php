<?php

namespace App\Infrastructure\Identity;

use App\Domain\Members\Contracts\IdentityProvider;
use App\Domain\Members\Data\Identity;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\AbstractProvider;

/** Google through Socialite. Only openid, email and profile are requested; the token is never stored. */
final class GoogleIdentityProvider implements IdentityProvider
{
    public const SCOPES = ['openid', 'email', 'profile'];

    public function redirectUrl(): string
    {
        return $this->driver()->redirect()->getTargetUrl();
    }

    public function identity(): Identity
    {
        $user = $this->driver()->user();

        return new Identity(
            providerId: (string) $user->getId(),
            name: (string) ($user->getName() ?: $user->getNickname() ?: 'Vigia'),
            email: (string) $user->getEmail(),
            avatarUrl: $user->getAvatar() ?: null,
        );
    }

    private function driver(): AbstractProvider
    {
        /** @var AbstractProvider $driver */
        $driver = Socialite::driver('google');

        return $driver->setScopes(self::SCOPES);
    }
}

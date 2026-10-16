<?php

namespace App\Infrastructure\Identity;

use App\Domain\Members\Contracts\IdTokenVerifier;
use App\Domain\Members\Data\Identity;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Validates the ID token against Google's tokeninfo endpoint, which already
 * checks the signature and expiry; the audience is checked here so a token
 * issued to another app can never sign in.
 */
final class GoogleIdTokenVerifier implements IdTokenVerifier
{
    private const TOKENINFO_URL = 'https://oauth2.googleapis.com/tokeninfo';

    public function verify(string $idToken): Identity
    {
        $response = Http::timeout(10)->get(self::TOKENINFO_URL, ['id_token' => $idToken]);
        if ($response->failed()) {
            throw new RuntimeException('Google rejected the ID token.');
        }

        $claims = (array) $response->json();
        if (($claims['aud'] ?? null) !== (string) config('services.google.client_id')) {
            throw new RuntimeException('ID token issued for another audience.');
        }
        if (! in_array($claims['iss'] ?? null, ['accounts.google.com', 'https://accounts.google.com'], true)) {
            throw new RuntimeException('ID token from an unexpected issuer.');
        }
        if (($claims['email_verified'] ?? null) !== 'true' || blank($claims['email'] ?? null)) {
            throw new RuntimeException('ID token without a verified e-mail.');
        }

        return new Identity(
            providerId: (string) $claims['sub'],
            name: (string) (($claims['name'] ?? null) ?: 'Vigia'),
            email: (string) $claims['email'],
            avatarUrl: ($claims['picture'] ?? null) ?: null,
        );
    }
}

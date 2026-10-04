<?php

namespace App\Infrastructure\Privacy;

use App\Domain\Privacy\Contracts\PrivacyPractices;
use App\Infrastructure\Identity\GoogleIdentityProvider;
use App\Infrastructure\Sightings\SightingPhotoUrls;

/** Texts from lang/pt_BR/privacy.php, numbers from the constants and settings that enforce them. */
final class CodePrivacyPractices implements PrivacyPractices
{
    public function title(): string
    {
        return (string) trans('privacy.title', [], 'pt_BR');
    }

    public function all(): array
    {
        /** @var list<string> $lines */
        $lines = trans('privacy.practices', [], 'pt_BR');
        $values = [
            ':minutes' => (string) SightingPhotoUrls::SIGNED_MINUTES,
            ':scopes' => implode(', ', GoogleIdentityProvider::SCOPES),
            ':years' => (string) config('privacy.fiscal_retention_years'),
            ':months' => (string) config('privacy.access_log_months'),
        ];

        return array_map(fn (string $line) => strtr($line, $values), $lines);
    }
}

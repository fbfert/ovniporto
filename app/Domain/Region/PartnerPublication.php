<?php

namespace App\Domain\Region;

use DateTimeInterface;

/**
 * A partner only appears publicly with recorded consent and a publication date in the past.
 */
final class PartnerPublication
{
    public static function isPublic(
        ?DateTimeInterface $consentGivenAt,
        ?DateTimeInterface $publishedAt,
        DateTimeInterface $now,
    ): bool {
        return $consentGivenAt !== null
            && $publishedAt !== null
            && $publishedAt <= $now;
    }

    /** Publishing is refused outright without consent, whatever the caller (panel, import, seed). */
    public static function assertCanBePublished(string $slug, ?DateTimeInterface $consentGivenAt): void
    {
        if ($consentGivenAt === null) {
            throw PartnerWithoutConsent::forPartner($slug);
        }
    }
}

<?php

namespace App\Domain\Members\Data;

/** What we keep from the identity provider: nothing beyond these four fields, and never the OAuth token. */
final readonly class Identity
{
    public function __construct(
        public string $providerId,
        public string $name,
        public string $email,
        public ?string $avatarUrl,
    ) {}
}

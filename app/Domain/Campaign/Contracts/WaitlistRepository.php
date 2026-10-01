<?php

namespace App\Domain\Campaign\Contracts;

interface WaitlistRepository
{
    /**
     * Stores a pending subscription. Returns the id, or null when the e-mail was already subscribed.
     */
    public function addIfAbsent(string $email, string $source, string $consentText): ?int;

    /** Marks the subscription as confirmed. Returns false when it does not exist. */
    public function confirm(int $id): bool;
}

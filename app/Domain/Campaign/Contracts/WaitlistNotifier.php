<?php

namespace App\Domain\Campaign\Contracts;

interface WaitlistNotifier
{
    /** Sends the double opt-in message. Implementations MUST queue it. */
    public function sendConfirmation(int $subscriptionId, string $email): void;
}

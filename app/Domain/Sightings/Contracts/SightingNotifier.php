<?php

namespace App\Domain\Sightings\Contracts;

/** E-mails around a report, always queued. */
interface SightingNotifier
{
    /** Confirmation to the author and a heads-up to the moderators. */
    public function submitted(int $sightingId): void;
}

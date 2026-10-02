<?php

namespace App\Domain\Sightings\Contracts;

/** E-mails around a report, always queued. */
interface SightingNotifier
{
    /** Confirmation to the author and a heads-up to the moderators. */
    public function submitted(int $sightingId): void;

    /** The report is in the Livro: link to its public page. */
    public function approved(int $sightingId): void;

    /** What the tower asks to adjust, with the link to edit and resend. */
    public function changesRequested(int $sightingId, string $message): void;

    public function rejected(int $sightingId, string $reason): void;
}

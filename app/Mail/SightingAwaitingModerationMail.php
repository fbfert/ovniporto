<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** Heads-up to moderators. Carries only the report id: details stay behind the panel's login. */
class SightingAwaitingModerationMail extends Mailable implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $sightingId) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "Novo relato #{$this->sightingId} na fila da torre");
    }

    public function content(): Content
    {
        return new Content(view: 'mail.sighting-awaiting-moderation');
    }
}

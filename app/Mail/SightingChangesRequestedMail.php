<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class SightingChangesRequestedMail extends Mailable implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $nickname, public string $note, public string $url) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'A torre pediu um ajuste no seu relato');
    }

    public function content(): Content
    {
        return new Content(view: 'mail.sighting-changes-requested');
    }
}

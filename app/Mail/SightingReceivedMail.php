<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class SightingReceivedMail extends Mailable implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $nickname) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Relato na torre de controle');
    }

    public function content(): Content
    {
        return new Content(view: 'mail.sighting-received');
    }
}

<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class SightingRejectedMail extends Mailable implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $nickname, public string $reason) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Seu relato não entrou no Livro');
    }

    public function content(): Content
    {
        return new Content(view: 'mail.sighting-rejected');
    }
}

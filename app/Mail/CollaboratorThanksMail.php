<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** Tells the person the offer arrived and what happens next. */
class CollaboratorThanksMail extends Mailable implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $name) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Recebemos sua oferta de ajuda na pesquisa');
    }

    public function content(): Content
    {
        return new Content(view: 'mail.collaborator-thanks');
    }
}

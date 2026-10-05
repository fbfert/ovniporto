<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** Heads-up to the admins. Carries only the id: name, e-mail and message stay behind the panel's login. */
class CollaboratorApplicationMail extends Mailable implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $collaboratorId, public string $panelUrl) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "Nova oferta de colaboração #{$this->collaboratorId}");
    }

    public function content(): Content
    {
        return new Content(view: 'mail.collaborator-application');
    }
}

<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** Heads-up to the team. Carries only the protocol: who asked and why stay behind the panel's login. */
class ReturnRequestedMail extends Mailable implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $protocol, public string $panelUrl) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "Novo pedido de devolução {$this->protocol}");
    }

    public function content(): Content
    {
        return new Content(view: 'mail.return-requested');
    }
}

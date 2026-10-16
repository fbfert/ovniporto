<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;

class ConfirmWaitlistMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public string $confirmUrl, public string $unsubscribeUrl) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Confirme: avise-me quando a pista abrir');
    }

    /** Mail apps show their own "unsubscribe": it opens the same confirmation page. */
    public function headers(): Headers
    {
        return new Headers(text: ['List-Unsubscribe' => "<{$this->unsubscribeUrl}>"]);
    }

    public function content(): Content
    {
        return new Content(view: 'mail.waitlist-confirm');
    }
}

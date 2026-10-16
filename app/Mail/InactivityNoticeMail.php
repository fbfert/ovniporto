<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class InactivityNoticeMail extends Mailable implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $nickname,
        public string $anonymizeOn,
        public string $url,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Sua conta no OVNIPORTO vai ser anonimizada');
    }

    public function content(): Content
    {
        return new Content(view: 'mail.inactivity-notice');
    }
}

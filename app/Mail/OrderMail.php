<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** Every order e-mail: one layout, the headline and lines change with the status. */
class OrderMail extends Mailable implements ShouldQueue
{
    use Queueable;

    /** @param list<string> $lines */
    public function __construct(
        public string $subjectLine,
        public string $headline,
        public array $lines,
        public string $url,
        public string $buttonLabel = 'Ver o pedido',
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->subjectLine);
    }

    public function content(): Content
    {
        return new Content(view: 'mail.order');
    }
}

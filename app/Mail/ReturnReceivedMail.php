<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** To the buyer: the protocol, what was asked and when the answer comes. */
class ReturnReceivedMail extends Mailable implements ShouldQueue
{
    use Queueable;

    public const ANSWER_DEADLINE = 'em até 5 dias úteis';

    public function __construct(
        public string $protocol,
        public string $orderNumber,
        public string $reason,
        public bool $withinWithdrawal,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "Pedido de devolução {$this->protocol} recebido");
    }

    public function content(): Content
    {
        return new Content(view: 'mail.return-received', with: ['deadline' => self::ANSWER_DEADLINE]);
    }
}

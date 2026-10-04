<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** Sent right away, not queued: the queue may be exactly what is broken. */
class JobFailedAlertMail extends Mailable
{
    public function __construct(
        public string $job,
        public int $attempts,
        public string $error,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "Torre: tarefa {$this->job} falhou {$this->attempts} vezes");
    }

    public function content(): Content
    {
        return new Content(view: 'mail.job-failed-alert');
    }
}

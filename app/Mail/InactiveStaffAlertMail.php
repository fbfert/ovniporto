<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class InactiveStaffAlertMail extends Mailable implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $nickname,
        public string $role,
        public string $lastSeenOn,
        public string $url,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "Torre: @{$this->nickname} está sem acesso há anos");
    }

    public function content(): Content
    {
        return new Content(view: 'mail.inactive-staff-alert');
    }
}

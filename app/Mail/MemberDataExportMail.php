<?php

namespace App\Mail;

use Illuminate\Mail\Attachment;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** Sent by the ExportMemberData job (already queued), so the mailable itself is not. */
class MemberDataExportMail extends Mailable
{
    public function __construct(public string $json) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Seus dados no OVNIPORTO');
    }

    public function content(): Content
    {
        return new Content(view: 'mail.member-data-export');
    }

    /** @return list<Attachment> */
    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => $this->json, 'meus-dados-ovniporto.json')->withMime('application/json'),
        ];
    }
}

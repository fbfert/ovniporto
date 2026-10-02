<?php

namespace App\Jobs;

use App\Application\Members\UseCases\BuildMemberExport;
use App\Mail\MemberDataExportMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

/** Builds the member's data export and e-mails it as a JSON attachment to the account address. */
class ExportMemberData implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly int $memberId) {}

    public function handle(BuildMemberExport $buildExport): void
    {
        $export = $buildExport->execute($this->memberId);
        if ($export === null) {
            return; // account deleted meanwhile: nothing left to send
        }

        $json = (string) json_encode($export, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        Mail::to($export['perfil']['email'])->send(new MemberDataExportMail($json));
    }
}

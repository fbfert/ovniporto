<?php

namespace App\Infrastructure\Mail;

use App\Domain\Members\MemberRole;
use App\Domain\Origin\Contracts\CollaboratorNotifier;
use App\Mail\CollaboratorApplicationMail;
use App\Mail\CollaboratorThanksMail;
use App\Models\Member;
use Illuminate\Support\Facades\Mail;

/** Both e-mails go through the queue. The team's carries only the id: details stay behind the panel's login. */
final class MailCollaboratorNotifier implements CollaboratorNotifier
{
    public function applied(int $collaboratorId, string $name, string $email): void
    {
        Mail::to($email)->queue(new CollaboratorThanksMail($name));

        Member::query()
            ->where('role', MemberRole::Admin->value)
            ->pluck('email')
            ->each(fn (string $admin) => Mail::to($admin)->queue(new CollaboratorApplicationMail($collaboratorId, route('panel.collaborators'))));
    }
}

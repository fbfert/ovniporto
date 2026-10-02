<?php

namespace App\Infrastructure\Mail;

use App\Domain\Members\MemberRole;
use App\Domain\Sightings\Contracts\SightingNotifier;
use App\Mail\SightingAwaitingModerationMail;
use App\Mail\SightingReceivedMail;
use App\Models\Member;
use App\Models\Sighting;
use Illuminate\Support\Facades\Mail;

/** Both e-mails go through the queue: submitting never waits on SMTP. */
final class MailSightingNotifier implements SightingNotifier
{
    public function submitted(int $sightingId): void
    {
        $sighting = Sighting::query()->with('member')->find($sightingId);
        if ($sighting === null) {
            return;
        }

        if ($sighting->member !== null) {
            Mail::to($sighting->member->email)->queue(new SightingReceivedMail($sighting->public_nickname));
        }

        Member::query()
            ->whereIn('role', [MemberRole::Moderator->value, MemberRole::Admin->value])
            ->pluck('email')
            ->each(fn (string $email) => Mail::to($email)->queue(new SightingAwaitingModerationMail($sighting->id)));
    }
}

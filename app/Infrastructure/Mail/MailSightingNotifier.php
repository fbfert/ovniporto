<?php

namespace App\Infrastructure\Mail;

use App\Domain\Members\MemberRole;
use App\Domain\Sightings\Contracts\SightingNotifier;
use App\Mail\SightingApprovedMail;
use App\Mail\SightingAwaitingModerationMail;
use App\Mail\SightingChangesRequestedMail;
use App\Mail\SightingReceivedMail;
use App\Mail\SightingRejectedMail;
use App\Models\Member;
use App\Models\Sighting;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;

/** Every e-mail goes through the queue: no moderation step ever waits on SMTP. */
final class MailSightingNotifier implements SightingNotifier
{
    public function submitted(int $sightingId): void
    {
        $sighting = $this->toAuthor($sightingId, fn (Sighting $s) => new SightingReceivedMail($s->public_nickname));
        if ($sighting === null) {
            return;
        }

        Member::query()
            ->whereIn('role', [MemberRole::Moderator->value, MemberRole::Admin->value])
            ->pluck('email')
            ->each(fn (string $email) => Mail::to($email)->queue(new SightingAwaitingModerationMail($sighting->id)));
    }

    public function approved(int $sightingId): void
    {
        $this->toAuthor($sightingId, fn (Sighting $s) => new SightingApprovedMail(
            $s->public_nickname,
            route('sightings.show', $s->id),
        ));
    }

    public function changesRequested(int $sightingId, string $message): void
    {
        $this->toAuthor($sightingId, fn (Sighting $s) => new SightingChangesRequestedMail(
            $s->public_nickname,
            $message,
            route('report.edit', $s->id),
        ));
    }

    public function rejected(int $sightingId, string $reason): void
    {
        $this->toAuthor($sightingId, fn (Sighting $s) => new SightingRejectedMail($s->public_nickname, $reason));
    }

    /** @param callable(Sighting): Mailable $mail */
    private function toAuthor(int $sightingId, callable $mail): ?Sighting
    {
        $sighting = Sighting::query()->with('member')->find($sightingId);
        if ($sighting?->member !== null) {
            Mail::to($sighting->member->email)->queue($mail($sighting));
        }

        return $sighting;
    }
}

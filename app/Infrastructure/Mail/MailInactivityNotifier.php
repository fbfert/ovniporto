<?php

namespace App\Infrastructure\Mail;

use App\Domain\Members\Contracts\InactivityNotifier;
use App\Domain\Members\MemberRole;
use App\Mail\InactiveStaffAlertMail;
use App\Mail\InactivityNoticeMail;
use App\Models\Member;
use DateTimeInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

/**
 * The notice goes to the member; the staff alert only to the admins, never to the alerts
 * address (decision of the encarregado: it is about people, not about the servers).
 */
final class MailInactivityNotifier implements InactivityNotifier
{
    private const ROLE_LABELS = ['moderator' => 'Moderação', 'store' => 'Loja', 'admin' => 'Admin'];

    public function noticeAnonymization(string $email, ?string $nickname, DateTimeInterface $anonymizeOn): void
    {
        Mail::to($email)->queue(new InactivityNoticeMail(
            $nickname ?? 'Oi',
            Carbon::instance($anonymizeOn)->locale('pt_BR')->translatedFormat('j \d\e F \d\e Y'),
            route('login'),
        ));
    }

    public function alertInactiveStaff(int $memberId, ?string $nickname, string $role, string $lastSeenAt): void
    {
        $admins = Member::query()
            ->where('role', MemberRole::Admin->value)
            ->whereNotNull('email')
            ->pluck('email')
            ->all();
        if ($admins === []) {
            return;
        }

        Mail::to($admins)->queue(new InactiveStaffAlertMail(
            $nickname ?? 'sem apelido',
            self::ROLE_LABELS[$role] ?? $role,
            Carbon::parse($lastSeenAt)->locale('pt_BR')->translatedFormat('j \d\e F \d\e Y'),
            route('panel.members.show', ['member' => $memberId]),
        ));
    }
}

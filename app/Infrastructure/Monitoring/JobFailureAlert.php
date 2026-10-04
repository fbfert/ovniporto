<?php

namespace App\Infrastructure\Monitoring;

use App\Domain\Members\MemberRole;
use App\Mail\JobFailedAlertMail;
use App\Models\Member;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

/**
 * A job that failed for the third time (the workers' "tries", config/horizon.php) e-mails the
 * alerts address, or every admin when none is set. A broken mailer must not hide the failure,
 * so a failed alert is only reported.
 */
final class JobFailureAlert
{
    public const ATTEMPTS = 3;

    public function handle(JobFailed $event): void
    {
        $attempts = $event->job->attempts();
        if ($attempts < self::ATTEMPTS) {
            return;
        }

        $recipients = $this->recipients();
        if ($recipients === []) {
            return;
        }

        try {
            Mail::to($recipients)->send(new JobFailedAlertMail(
                class_basename($event->job->resolveName()),
                $attempts,
                Str::limit($event->exception->getMessage(), 300),
            ));
        } catch (Throwable $e) {
            report($e);
        }
    }

    /** @return list<string> */
    private function recipients(): array
    {
        $configured = config('ovniporto.alerts_email');
        if (filled($configured)) {
            return [(string) $configured];
        }

        return Member::query()->where('role', MemberRole::Admin->value)->pluck('email')->filter()->values()->all();
    }
}

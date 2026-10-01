<?php

namespace App\Infrastructure\Mail;

use App\Domain\Campaign\Contracts\WaitlistNotifier;
use App\Mail\ConfirmWaitlistMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

final class MailWaitlistNotifier implements WaitlistNotifier
{
    public function sendConfirmation(int $subscriptionId, string $email): void
    {
        $url = URL::temporarySignedRoute(
            'waitlist.confirm',
            now()->addDays(7),
            ['subscriber' => $subscriptionId],
        );

        Mail::to($email)->queue(new ConfirmWaitlistMail($url));
    }
}

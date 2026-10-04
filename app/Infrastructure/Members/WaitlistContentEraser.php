<?php

namespace App\Infrastructure\Members;

use App\Domain\Members\Contracts\MemberContentEraser;
use App\Models\NewsletterSubscriber;

/**
 * Account deletion, Campaign side: the "Avise-me" sign-up of the account e-mail goes too.
 * Its consent is anonymized with the rest, so the list could no longer prove it.
 */
final class WaitlistContentEraser implements MemberContentEraser
{
    public function eraseFor(int $memberId, string $email): void
    {
        NewsletterSubscriber::query()->where('email', mb_strtolower($email))->delete();
    }
}

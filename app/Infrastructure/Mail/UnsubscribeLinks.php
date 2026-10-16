<?php

namespace App\Infrastructure\Mail;

use App\Domain\Privacy\MailingList;
use Illuminate\Support\Facades\URL;

/** The link at the bottom of every list e-mail: signed with the app key, never expires, no sign-in. */
final class UnsubscribeLinks
{
    public static function to(MailingList $list, int $id): string
    {
        return URL::signedRoute('unsubscribe.show', ['list' => $list->value, 'id' => $id]);
    }
}

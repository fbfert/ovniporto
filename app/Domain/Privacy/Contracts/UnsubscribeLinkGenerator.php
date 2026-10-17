<?php

namespace App\Domain\Privacy\Contracts;

use App\Domain\Privacy\MailingList;

/** The personal link that lets someone leave a mailing list without signing in. */
interface UnsubscribeLinkGenerator
{
    public function for(MailingList $list, int $id): string;
}

<?php

namespace App\Domain\Settings\Contracts;

use App\Domain\Settings\Data\MailTestResult;

/** Sends one message right now, through SMTP and without the queue, and says how it went. */
interface MailTester
{
    /** @param list<string> $to */
    public function send(array $to, string $subject, string $body): MailTestResult;
}

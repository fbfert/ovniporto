<?php

namespace App\Domain\Privacy;

/** "a***@serra.com": enough to recognize the address, not enough to read it from a forwarded e-mail. */
final class EmailMask
{
    public static function of(string $email): string
    {
        [$local, $domain] = array_pad(explode('@', $email, 2), 2, '');

        return mb_substr($local, 0, 1).'***@'.$domain;
    }
}

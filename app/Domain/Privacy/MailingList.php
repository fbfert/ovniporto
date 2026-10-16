<?php

namespace App\Domain\Privacy;

/**
 * The lists someone can leave by a link in the e-mail: the Avise-me and the offers of help
 * to the origin research. The value is the one in the unsubscribe URL.
 */
enum MailingList: string
{
    case Waitlist = 'avise-me';
    case Collaboration = 'colaboracao';

    public function consentType(): ConsentType
    {
        return match ($this) {
            self::Waitlist => ConsentType::Newsletter,
            self::Collaboration => ConsentType::Collaboration,
        };
    }

    /** The subject the consent was recorded with when the person signed up. */
    public function consentSubject(int $id): string
    {
        return match ($this) {
            self::Waitlist => "avise-me:{$id}",
            self::Collaboration => "colaborador:{$id}",
        };
    }
}

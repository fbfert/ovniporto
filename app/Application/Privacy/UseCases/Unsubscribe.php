<?php

namespace App\Application\Privacy\UseCases;

use App\Domain\Campaign\Contracts\WaitlistRepository;
use App\Domain\Origin\Contracts\CollaboratorRepository;
use App\Domain\Privacy\Contracts\ConsentLedger;
use App\Domain\Privacy\EmailMask;
use App\Domain\Privacy\MailingList;

/**
 * Leaving the Avise-me or the collaboration by the link in the e-mail, without signing in.
 * Showing what will be canceled changes nothing; only confirming removes the sign-up and
 * marks its consent as withdrawn. A sign-up that is already gone is not an error.
 */
final readonly class Unsubscribe
{
    public function __construct(
        private WaitlistRepository $waitlist,
        private CollaboratorRepository $collaborators,
        private ConsentLedger $consents,
    ) {}

    /** The masked e-mail of the sign-up, or null when there is nothing left to cancel. */
    public function preview(MailingList $list, int $id): ?string
    {
        $email = match ($list) {
            MailingList::Waitlist => $this->waitlist->emailOf($id),
            MailingList::Collaboration => $this->collaborators->emailOf($id),
        };

        return $email === null ? null : EmailMask::of($email);
    }

    public function execute(MailingList $list, int $id): void
    {
        match ($list) {
            MailingList::Waitlist => $this->waitlist->remove($id),
            MailingList::Collaboration => $this->collaborators->remove($id),
        };
        $this->consents->withdraw($list->consentType(), $list->consentSubject($id));
    }
}

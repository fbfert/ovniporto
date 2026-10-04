<?php

namespace App\Application\Campaign\UseCases;

use App\Domain\Campaign\Contracts\WaitlistNotifier;
use App\Domain\Campaign\Contracts\WaitlistRepository;
use App\Domain\Privacy\ConsentType;
use App\Domain\Privacy\Contracts\ConsentLedger;

/**
 * Idempotent: subscribing twice yields the same outcome and never reveals
 * whether the address was already on the list.
 */
final readonly class SubscribeToWaitlist
{
    public const CONSENT_TEXT = 'Quero receber um e-mail quando a campanha da pista do OVNIPORTO abrir. Posso sair a qualquer momento.';

    public function __construct(
        private WaitlistRepository $waitlist,
        private WaitlistNotifier $notifier,
        private ConsentLedger $consents,
    ) {}

    public function execute(string $email, string $source): void
    {
        $email = mb_strtolower(trim($email));
        $id = $this->waitlist->addIfAbsent($email, $source, self::CONSENT_TEXT);

        if ($id !== null) {
            $this->consents->record(ConsentType::Newsletter, ConsentType::Newsletter->textVersion(), email: $email, subject: "avise-me:{$id}");
            $this->notifier->sendConfirmation($id, $email);
        }
    }
}

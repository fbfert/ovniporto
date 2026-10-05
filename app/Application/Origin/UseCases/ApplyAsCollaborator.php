<?php

namespace App\Application\Origin\UseCases;

use App\Domain\Origin\CollaborationArea;
use App\Domain\Origin\Contracts\CollaboratorNotifier;
use App\Domain\Origin\Contracts\CollaboratorRepository;
use App\Domain\Privacy\ConsentType;
use App\Domain\Privacy\Contracts\ConsentLedger;

/** "Seja colaborador" on /origem/atlas: stores the offer with its consent, thanks the person and warns the team. */
final readonly class ApplyAsCollaborator
{
    public const CONSENT_TEXT = 'Autorizo o OVNIPORTO a guardar estes dados e a me escrever sobre a pesquisa da origem. Posso pedir a exclusão a qualquer momento.';

    public function __construct(
        private CollaboratorRepository $collaborators,
        private CollaboratorNotifier $notifier,
        private ConsentLedger $consents,
    ) {}

    /** @param list<string> $areas values of CollaborationArea; unknown ones are dropped */
    public function execute(string $name, string $email, string $location, array $areas, string $message): int
    {
        $name = trim($name);
        $email = mb_strtolower(trim($email));
        $known = array_values(array_filter(array_map(CollaborationArea::tryFrom(...), array_unique($areas))));

        $id = $this->collaborators->add($name, $email, trim($location), $known, trim($message), self::CONSENT_TEXT);
        $this->consents->record(ConsentType::Collaboration, ConsentType::Collaboration->textVersion(), email: $email, subject: "colaborador:{$id}");
        $this->notifier->applied($id, $name, $email);

        return $id;
    }
}

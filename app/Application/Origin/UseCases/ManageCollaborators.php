<?php

namespace App\Application\Origin\UseCases;

use App\Domain\Audit\Contracts\Auditor;
use App\Domain\Audit\Data\AuditEntry;
use App\Domain\Origin\Contracts\CollaboratorRepository;
use App\Domain\Privacy\Contracts\ConsentLedger;
use App\Domain\Privacy\Contracts\UnsubscribeLinkGenerator;
use App\Domain\Privacy\MailingList;

/** /painel/colaboradores: who offered to help the origin research. */
final readonly class ManageCollaborators
{
    public const CSV_HEADER = ['nome', 'email', 'cidade_pais', 'como_ajudar', 'mensagem', 'consentiu_em', 'link_descadastro'];

    public function __construct(
        private CollaboratorRepository $collaborators,
        private Auditor $auditor,
        private ConsentLedger $consents,
        private UnsubscribeLinkGenerator $unsubscribeLinks,
    ) {}

    /** @return array{collaborators: list<array<string, mixed>>} */
    public function page(): array
    {
        return ['collaborators' => $this->collaborators->collaborators()];
    }

    /**
     * Replies to the offers go out by hand, so each row carries the person's own unsubscribe link.
     *
     * @return list<list<string>> rows for the CSV, header first
     */
    public function export(): array
    {
        return [
            self::CSV_HEADER,
            ...array_map(
                fn (array $c) => [
                    $c['name'],
                    $c['email'],
                    $c['location'],
                    implode(', ', $c['areas']),
                    $c['message'],
                    $c['consentedAt'],
                    $this->unsubscribeLinks->for(MailingList::Collaboration, $c['id']),
                ],
                $this->collaborators->collaborators(),
            ),
        ];
    }

    /** At the person's request: the sign-up goes and its consent is marked as withdrawn. */
    public function remove(int $actorId, int $id): void
    {
        // Name and e-mail stay out of the audit log: removing them is the point.
        $this->auditor->audited(
            new AuditEntry($actorId, 'collaborator.removed', 'collaborator', $id),
            fn () => null,
            function () use ($id) {
                $this->collaborators->remove($id);
                $this->consents->withdraw(MailingList::Collaboration->consentType(), MailingList::Collaboration->consentSubject($id));
            },
        );
    }
}

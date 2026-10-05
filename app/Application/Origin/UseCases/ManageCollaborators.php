<?php

namespace App\Application\Origin\UseCases;

use App\Domain\Audit\Contracts\Auditor;
use App\Domain\Audit\Data\AuditEntry;
use App\Domain\Origin\Contracts\CollaboratorRepository;

/** /painel/colaboradores: who offered to help the origin research. */
final readonly class ManageCollaborators
{
    public const CSV_HEADER = ['nome', 'email', 'cidade_pais', 'como_ajudar', 'mensagem', 'consentiu_em'];

    public function __construct(
        private CollaboratorRepository $collaborators,
        private Auditor $auditor,
    ) {}

    /** @return array{collaborators: list<array<string, mixed>>} */
    public function page(): array
    {
        return ['collaborators' => $this->collaborators->collaborators()];
    }

    /** @return list<list<string>> rows for the CSV, header first */
    public function export(): array
    {
        return [
            self::CSV_HEADER,
            ...array_map(
                fn (array $c) => [$c['name'], $c['email'], $c['location'], implode(', ', $c['areas']), $c['message'], $c['consentedAt']],
                $this->collaborators->collaborators(),
            ),
        ];
    }

    public function remove(int $actorId, int $id): void
    {
        // Name and e-mail stay out of the audit log: removing them is the point.
        $this->auditor->audited(
            new AuditEntry($actorId, 'collaborator.removed', 'collaborator', $id),
            fn () => null,
            fn () => $this->collaborators->remove($id),
        );
    }
}

<?php

use App\Application\Origin\UseCases\ManageCollaborators;
use App\Domain\Audit\Contracts\Auditor;
use App\Domain\Audit\Data\AuditEntry;
use App\Domain\Origin\Contracts\CollaboratorRepository;

function collaboratorList(): CollaboratorRepository
{
    return new class implements CollaboratorRepository
    {
        /** @var list<int> */
        public array $removed = [];

        public function add(string $name, string $email, string $location, array $areas, string $message, string $consentText): int
        {
            return 1;
        }

        public function collaborators(): array
        {
            return [[
                'id' => 7, 'name' => 'Ana', 'email' => 'ana@serra.com', 'location' => 'Lages, Brasil',
                'areas' => ['pesquisa', 'fotos'], 'message' => 'Tenho fotos.', 'consentedAt' => '2026-10-05T10:00:00+00:00',
            ]];
        }

        public function remove(int $id): ?string
        {
            $this->removed[] = $id;

            return 'ana@serra.com';
        }
    };
}

function collaboratorAuditor(): Auditor
{
    return new class implements Auditor
    {
        /** @var list<AuditEntry> */
        public array $entries = [];

        public function audited(AuditEntry $entry, callable $snapshot, callable $change): mixed
        {
            $this->entries[] = $entry;

            return $change();
        }

        public function history(string $subjectType, int $subjectId): array
        {
            return [];
        }

        public function recent(int $page, int $perPage): array
        {
            return ['items' => [], 'hasMore' => false];
        }
    };
}

it('exports a header and one row per offer, areas joined', function () {
    $rows = (new ManageCollaborators(collaboratorList(), collaboratorAuditor()))->export();

    expect($rows[0])->toBe(ManageCollaborators::CSV_HEADER)
        ->and($rows[1])->toBe(['Ana', 'ana@serra.com', 'Lages, Brasil', 'pesquisa, fotos', 'Tenho fotos.', '2026-10-05T10:00:00+00:00']);
});

it('removes an offer through the audit log, without the e-mail in it', function () {
    $repository = collaboratorList();
    $auditor = collaboratorAuditor();

    (new ManageCollaborators($repository, $auditor))->remove(3, 7);

    expect($repository->removed)->toBe([7])
        ->and($auditor->entries[0]->action)->toBe('collaborator.removed')
        ->and(json_encode($auditor->entries[0]))->not->toContain('ana@serra.com');
});

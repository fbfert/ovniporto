<?php

use App\Application\Origin\UseCases\ApplyAsCollaborator;
use App\Domain\Origin\CollaborationArea;
use App\Domain\Origin\Contracts\CollaboratorNotifier;
use App\Domain\Origin\Contracts\CollaboratorRepository;
use App\Domain\Privacy\ConsentType;
use Tests\Support\SpyConsentLedger;

function fakeCollaborators(): CollaboratorRepository
{
    return new class implements CollaboratorRepository
    {
        /** @var list<array{name: string, email: string, location: string, areas: list<CollaborationArea>, message: string, consentText: string}> */
        public array $rows = [];

        public function add(string $name, string $email, string $location, array $areas, string $message, string $consentText): int
        {
            $this->rows[] = compact('name', 'email', 'location', 'areas', 'message', 'consentText');

            return count($this->rows);
        }

        public function collaborators(): array
        {
            return [];
        }

        public function remove(int $id): ?string
        {
            return null;
        }
    };
}

function spyCollaboratorNotifier(): CollaboratorNotifier
{
    return new class implements CollaboratorNotifier
    {
        /** @var list<array{id: int, name: string, email: string}> */
        public array $sent = [];

        public function applied(int $collaboratorId, string $name, string $email): void
        {
            $this->sent[] = ['id' => $collaboratorId, 'name' => $name, 'email' => $email];
        }
    };
}

it('stores the trimmed offer, keeps only known areas and notifies once', function () {
    $repository = fakeCollaborators();
    $notifier = spyCollaboratorNotifier();

    $id = (new ApplyAsCollaborator($repository, $notifier, new SpyConsentLedger))
        ->execute('  Ana  ', ' Ana@Serra.com ', ' Lages, Brasil ', ['traducao', 'vistoria', 'traducao', 'hacker'], ' Leio espanhol. ');

    $row = $repository->rows[0];
    expect($id)->toBe(1)
        ->and($row['name'])->toBe('Ana')
        ->and($row['email'])->toBe('ana@serra.com')
        ->and($row['location'])->toBe('Lages, Brasil')
        ->and($row['areas'])->toBe([CollaborationArea::Translation, CollaborationArea::Fieldwork])
        ->and($row['message'])->toBe('Leio espanhol.')
        ->and($row['consentText'])->toBe(ApplyAsCollaborator::CONSENT_TEXT)
        ->and($notifier->sent)->toBe([['id' => 1, 'name' => 'Ana', 'email' => 'ana@serra.com']]);
});

it('records the collaboration consent with its version and the application', function () {
    $consents = new SpyConsentLedger;

    (new ApplyAsCollaborator(fakeCollaborators(), spyCollaboratorNotifier(), $consents))
        ->execute('Ana', 'ana@serra.com', 'Lages', ['pesquisa'], 'Tenho recortes de jornal.');

    expect($consents->records)->toHaveCount(1)
        ->and($consents->records[0]['type'])->toBe(ConsentType::Collaboration)
        ->and($consents->records[0]['version'])->toBe(ConsentType::Collaboration->textVersion())
        ->and($consents->records[0]['email'])->toBe('ana@serra.com')
        ->and($consents->records[0]['subject'])->toBe('colaborador:1');
});

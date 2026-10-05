<?php

use App\Mail\CollaboratorApplicationMail;
use App\Mail\CollaboratorThanksMail;
use App\Models\Consent;
use App\Models\Member;
use App\Models\ResearchCollaborator;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(fn () => Mail::fake());

/** @return array<string, mixed> */
function collaboratorPayload(array $overrides = []): array
{
    return [
        'name' => 'Ana Vigia',
        'email' => 'Ana@Serra.com',
        'location' => 'Lages, Brasil',
        'areas' => ['pesquisa', 'vistoria'],
        'message' => 'Moro perto de um dos casos e posso visitar.',
        'consent' => '1',
        ...$overrides,
    ];
}

it('stores the offer with consent, thanks the person and warns the admins', function () {
    $admin = Member::factory()->role('admin')->create();
    Member::factory()->role('moderator')->create();

    $this->from('/origem/atlas')->post('/colaborar', collaboratorPayload())
        ->assertRedirect('/origem/atlas')
        ->assertSessionHas('toast');

    $collaborator = ResearchCollaborator::query()->sole();
    expect($collaborator->email)->toBe('ana@serra.com')
        ->and($collaborator->areas)->toBe(['pesquisa', 'vistoria'])
        ->and($collaborator->consent_text)->not->toBeEmpty()
        ->and(Consent::query()->where('type', 'colaboracao')->where('subject', "colaborador:{$collaborator->id}")->count())->toBe(1);

    Mail::assertQueued(CollaboratorThanksMail::class, fn ($mail) => $mail->hasTo('ana@serra.com'));
    Mail::assertQueued(CollaboratorApplicationMail::class, 1);
    Mail::assertQueued(CollaboratorApplicationMail::class, fn ($mail) => $mail->hasTo($admin->email));
});

it('requires consent, at least one known area and a message, with Portuguese messages', function () {
    $this->from('/origem/atlas')->post('/colaborar', collaboratorPayload(['consent' => null, 'areas' => [], 'message' => 'oi']))
        ->assertSessionHasErrors([
            'consent' => 'Marque a caixa para a gente poder guardar seus dados e responder.',
            'areas' => 'Escolha pelo menos uma forma de ajudar.',
            'message' => 'Conte um pouco mais: pelo menos 10 caracteres.',
        ]);
    $this->from('/origem/atlas')->post('/colaborar', collaboratorPayload(['areas' => ['hacker']]))
        ->assertSessionHasErrors('areas.0');

    expect(ResearchCollaborator::query()->count())->toBe(0);
    Mail::assertNothingQueued();
});

it('answers a filled honeypot like a person and stores nothing', function () {
    $this->from('/origem/atlas')->post('/colaborar', collaboratorPayload(['website' => 'http://spam.example']))
        ->assertRedirect('/origem/atlas')
        ->assertSessionHas('toast');

    expect(ResearchCollaborator::query()->count())->toBe(0);
    Mail::assertNothingQueued();
});

it('limits how often one address can apply', function () {
    foreach (range(1, 5) as $i) {
        $this->from('/origem/atlas')->post('/colaborar', collaboratorPayload(['email' => "ana{$i}@serra.com"]));
    }

    $this->from('/origem/atlas')->post('/colaborar', collaboratorPayload())->assertStatus(429);
});

it('lists, exports and removes offers in the admin panel only', function () {
    $admin = Member::factory()->role('admin')->create();
    $keep = ResearchCollaborator::query()->create(['name' => 'Fica', 'email' => 'fica@example.com', 'location' => 'Cachi, Argentina', 'areas' => ['fotos'], 'message' => 'Tenho fotos.', 'consent_text' => 'ok', 'consented_at' => now()]);
    $gone = ResearchCollaborator::query()->create(['name' => 'Sai', 'email' => 'sai@example.com', 'location' => 'Lages', 'areas' => ['outro'], 'message' => 'Quero sair.', 'consent_text' => 'ok', 'consented_at' => now()]);

    $this->actingAs(Member::factory()->role('moderator')->create())->get('/painel/colaboradores')->assertForbidden();

    $this->actingAs($admin)->get('/painel/colaboradores')
        ->assertInertia(fn (Assert $page) => $page->component('Panel/Content/Collaborators')->has('collaborators', 2));
    $this->delete("/painel/colaboradores/{$gone->id}")->assertSessionHasNoErrors();

    $csv = $this->get('/painel/colaboradores/exportar')->assertOk()->streamedContent();
    expect($csv)->toContain('fica@example.com')->not->toContain('sai@example.com');
    $this->get('/painel/colaboradores')->assertInertia(fn (Assert $page) => $page->has('collaborators', 1)->where('collaborators.0.id', $keep->id));
});

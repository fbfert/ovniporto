<?php

use App\Models\AuditLog;
use App\Models\Member;
use App\Models\Sighting;
use Inertia\Testing\AssertableInertia as Assert;

it('sends guests to sign in', function () {
    $this->get('/painel')->assertRedirect('/entrar');
});

it('refuses common members', function () {
    $this->actingAs(Member::factory()->create())->get('/painel')->assertForbidden();
    $this->get('/painel/relatos')->assertForbidden();
});

it('keeps each role inside its areas', function (string $role, array $allowed, array $forbidden) {
    $this->actingAs(Member::factory()->role($role)->create());

    foreach ($allowed as $path) {
        $this->get($path)->assertOk();
    }
    foreach ($forbidden as $path) {
        $this->get($path)->assertForbidden();
    }
})->with([
    'moderator' => ['moderator', ['/painel', '/painel/relatos', '/painel/membros'], ['/painel/pedidos', '/painel/produtos', '/painel/conteudo', '/painel/auditoria']],
    'store' => ['store', ['/painel', '/painel/pedidos', '/painel/produtos'], ['/painel/relatos', '/painel/membros', '/painel/auditoria']],
    'admin' => ['admin', ['/painel', '/painel/relatos', '/painel/pedidos', '/painel/conteudo', '/painel/auditoria'], []],
]);

it('shares only the areas the role may open', function () {
    $this->actingAs(Member::factory()->role('moderator')->create())
        ->get('/painel')
        ->assertInertia(fn (Assert $page) => $page
            ->component('Panel/Home')
            ->where('panelAreas', ['inicio', 'relatos', 'membros'])
        );
});

it('does not share panel areas on public pages', function () {
    $this->actingAs(Member::factory()->role('admin')->create())
        ->get('/')
        ->assertInertia(fn (Assert $page) => $page->where('panelAreas', null));
});

it('flags a report waiting for more than 48 hours', function () {
    $moderator = Member::factory()->role('moderator')->create();
    Sighting::factory()->create(['submitted_at' => now()->subHours(47)]);
    $this->actingAs($moderator)->get('/painel')->assertInertia(fn (Assert $page) => $page->where('sightings.overdue', false));

    Sighting::factory()->create(['submitted_at' => now()->subHours(49)]);
    $this->get('/painel')->assertInertia(fn (Assert $page) => $page
        ->where('sightings.overdue', true)
        ->where('sightings.pending', 2)
    );
});

it('shows the audit trail to admins, newest first', function () {
    $admin = Member::factory()->role('admin')->create();
    AuditLog::query()->create(['actor_id' => $admin->id, 'action' => 'sighting.approved', 'subject_type' => 'sighting', 'subject_id' => 1]);
    AuditLog::query()->create(['actor_id' => $admin->id, 'action' => 'sighting.rejected', 'subject_type' => 'sighting', 'subject_id' => 2]);

    $this->actingAs($admin)->get('/painel/auditoria')->assertInertia(fn (Assert $page) => $page
        ->component('Panel/Audit')
        ->has('items', 2)
        ->where('items.0.action', 'sighting.rejected')
        ->where('items.0.actor', $admin->nickname)
    );
});

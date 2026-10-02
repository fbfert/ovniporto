<?php

use App\Models\AuditLog;
use App\Models\Member;
use App\Models\Sighting;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    Mail::fake();
    $this->admin = Member::factory()->role('admin')->create();
    $this->moderator = Member::factory()->role('moderator')->create();
    $this->member = Member::factory()->create(['nickname' => 'coruja', 'name' => 'Maria Real', 'email' => 'maria@example.com']);
});

it('lists members with search and filters taken from the URL', function () {
    Member::factory()->create(['nickname' => 'farol']);

    $this->actingAs($this->moderator)->get('/painel/membros?busca=maria')->assertInertia(fn (Assert $page) => $page
        ->component('Panel/Members/Index')
        ->where('total', 1)
        ->where('items.0.nickname', 'coruja')
        ->where('filters.busca', 'maria')
    );

    $this->get('/painel/membros?papel=admin')->assertInertia(fn (Assert $page) => $page
        ->where('total', 1)
        ->where('items.0.id', $this->admin->id)
    );
});

it('refuses a role change by a moderator', function () {
    $this->actingAs($this->moderator)
        ->put("/painel/membros/{$this->member->id}/papel", ['role' => 'admin'])
        ->assertSessionHasErrors('role');

    expect($this->member->fresh()->role->value)->toBe('member');
});

it('lets the admin change a role, audited', function () {
    $this->actingAs($this->admin)
        ->put("/painel/membros/{$this->member->id}/papel", ['role' => 'moderator'])
        ->assertSessionHasNoErrors();

    expect($this->member->fresh()->role->value)->toBe('moderator');
    $log = AuditLog::query()->where('action', 'member.role_changed')->sole();
    expect($log->actor_id)->toBe($this->admin->id)
        ->and($log->before['role'])->toBe('member')
        ->and($log->after['role'])->toBe('moderator');
});

it('does not let the admin change their own role', function () {
    $this->actingAs($this->admin)
        ->put("/painel/membros/{$this->admin->id}/papel", ['role' => 'member'])
        ->assertSessionHasErrors('role');
});

it('blocks with a reason, and the blocked member cannot report', function () {
    $this->actingAs($this->moderator)->post("/painel/membros/{$this->member->id}/bloqueio", ['reason' => ''])->assertSessionHasErrors('reason');

    $this->post("/painel/membros/{$this->member->id}/bloqueio", ['reason' => 'Fotos com rostos de vizinhos, várias vezes.'])
        ->assertSessionHasNoErrors();
    expect($this->member->fresh())->blocked_reason->toBe('Fotos com rostos de vizinhos, várias vezes.');

    $this->actingAs($this->member->fresh());
    $this->get('/relatar')->assertRedirect('/conta');
    $this->post('/relatar', [
        'type' => 'light',
        'description' => 'Uma luz verde parada sobre a serra, depois sumiu.',
        'observedDate' => now()->toDateString(),
        'timeRange' => 'night',
        'lat' => -27.85,
        'lng' => -50.22,
        'nickname' => 'coruja',
        'consent' => '1',
    ])->assertForbidden();
    expect(Sighting::query()->count())->toBe(0);
});

it('does not block someone who still has a panel role', function () {
    $this->actingAs($this->admin)
        ->post("/painel/membros/{$this->moderator->id}/bloqueio", ['reason' => 'Motivo qualquer bem descrito.'])
        ->assertSessionHasErrors('reason');
});

it('unblocks', function () {
    $this->member->update(['blocked_at' => now(), 'blocked_reason' => 'Motivo antigo registrado.']);

    $this->actingAs($this->moderator)->delete("/painel/membros/{$this->member->id}/bloqueio")->assertSessionHasNoErrors();

    expect($this->member->fresh()->blocked_at)->toBeNull();
});

it('lets only the admin delete an account, with its reports', function () {
    Storage::fake('local');
    Storage::fake('public');
    Sighting::factory()->for($this->member)->create();

    $this->actingAs($this->moderator)->delete("/painel/membros/{$this->member->id}")->assertSessionHasErrors('delete');
    expect(Member::query()->find($this->member->id))->not->toBeNull();

    $this->actingAs($this->admin)->delete("/painel/membros/{$this->member->id}")->assertRedirect('/painel/membros');
    expect(Member::query()->find($this->member->id))->toBeNull()
        ->and(Sighting::query()->count())->toBe(0)
        ->and(AuditLog::query()->where('action', 'member.deleted')->exists())->toBeTrue();
});

it('keeps the member list away from the store role', function () {
    $this->actingAs(Member::factory()->role('store')->create())->get('/painel/membros')->assertForbidden();
});

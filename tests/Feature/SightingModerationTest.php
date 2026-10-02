<?php

use App\Domain\Map\Contracts\Geocoder;
use App\Jobs\LocateSighting;
use App\Mail\SightingApprovedMail;
use App\Mail\SightingChangesRequestedMail;
use App\Mail\SightingRejectedMail;
use App\Models\AuditLog;
use App\Models\Member;
use App\Models\Sighting;
use App\Models\SightingPhoto;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    Mail::fake();
    Storage::fake('local');
    $this->author = Member::factory()->create(['name' => 'Maria Real', 'email' => 'maria@example.com', 'nickname' => 'coruja']);
    $this->moderator = Member::factory()->role('moderator')->create();
});

function processedPhoto(Sighting $sighting): SightingPhoto
{
    $photo = $sighting->photos()->create(['path' => 'tmp', 'width' => 1600, 'height' => 1200, 'sort_order' => 0]);
    $base = "sightings/{$sighting->id}/{$photo->id}";
    foreach ([400, 800, 1600] as $w) {
        Storage::disk('local')->put("{$base}-{$w}.webp", 'webp');
    }
    $photo->update(['path' => $base, 'variants' => [400, 800, 1600], 'processed_at' => now()]);

    return $photo;
}

it('lists pending reports oldest first by default', function () {
    $newer = Sighting::factory()->create(['submitted_at' => now()->subHour()]);
    $older = Sighting::factory()->create(['submitted_at' => now()->subDays(2)]);
    Sighting::factory()->approved()->create();

    $this->actingAs($this->moderator)->get('/painel/relatos')->assertInertia(fn (Assert $page) => $page
        ->component('Panel/Sightings/Queue')
        ->where('tab', 'pendentes')
        ->where('counts.pendentes', 2)
        ->where('counts.aprovados', 1)
        ->has('items', 2)
        ->where('items.0.id', $older->id)
        ->where('items.1.id', $newer->id)
    );
});

it('opens the other tabs from the URL', function () {
    $approved = Sighting::factory()->approved()->create();
    Sighting::factory()->create();

    $this->actingAs($this->moderator)->get('/painel/relatos?aba=aprovados')->assertInertia(fn (Assert $page) => $page
        ->where('tab', 'aprovados')
        ->has('items', 1)
        ->where('items.0.id', $approved->id)
    );
});

it('shows the author real data and short-lived signed photos only on the review screen', function () {
    $sighting = Sighting::factory()->for($this->author)->create(['public_nickname' => 'coruja']);
    processedPhoto($sighting);

    $response = $this->actingAs($this->moderator)->get("/painel/relatos/{$sighting->id}");
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Panel/Sightings/Review')
        ->where('sighting.author.name', 'Maria Real')
        ->where('sighting.author.email', 'maria@example.com')
        ->has('sighting.photos', 1)
    );

    $url = $response->viewData('page')['props']['sighting']['photos'][0]['full'];
    expect($url)->toContain('signature=')->toContain('expires=');
    $this->get($url)->assertOk();
    $this->get((string) strtok($url, '?'))->assertNotFound();
});

it('approves: the report goes public at once and the author gets an e-mail', function () {
    $sighting = Sighting::factory()->for($this->author)->create();
    $this->getJson('/api/sightings')->assertJsonCount(0, 'data');

    $this->actingAs($this->moderator)->post("/painel/relatos/{$sighting->id}/aprovar")->assertRedirect('/painel/relatos');

    expect($sighting->fresh())
        ->status->value->toBe('approved')
        ->published_at->not->toBeNull()
        ->moderated_by->toBe($this->moderator->id);
    $this->getJson('/api/sightings')->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $sighting->id);
    Mail::assertQueued(SightingApprovedMail::class, fn ($mail) => $mail->hasTo('maria@example.com')
        && $mail->url === route('sightings.show', $sighting->id));
});

it('moves on to the next pending report after a decision', function () {
    $first = Sighting::factory()->create(['submitted_at' => now()->subDays(2)]);
    $second = Sighting::factory()->create(['submitted_at' => now()->subDay()]);

    $this->actingAs($this->moderator)
        ->post("/painel/relatos/{$first->id}/aprovar")
        ->assertRedirect("/painel/relatos/{$second->id}")
        ->assertSessionHas('toast');
});

it('requires a reason to reject', function () {
    $sighting = Sighting::factory()->for($this->author)->create();

    $this->actingAs($this->moderator)->post("/painel/relatos/{$sighting->id}/rejeitar", [])->assertSessionHasErrors('reason');
    $this->post("/painel/relatos/{$sighting->id}/rejeitar", ['reason' => 'other'])->assertSessionHasErrors('detail');

    expect($sighting->fresh()->status->value)->toBe('pending');
    Mail::assertNothingQueued();
});

it('rejects with the reason and tells the author', function () {
    $sighting = Sighting::factory()->for($this->author)->create();

    $this->actingAs($this->moderator)->post("/painel/relatos/{$sighting->id}/rejeitar", ['reason' => 'license_plate']);

    expect($sighting->fresh())->status->value->toBe('rejected')->moderation_note->toBe('Foto com placa de carro');
    Mail::assertQueued(SightingRejectedMail::class, fn ($mail) => $mail->reason === 'Foto com placa de carro');
});

it('takes a report off every public view when changes are requested', function () {
    $sighting = Sighting::factory()->for($this->author)->approved()->create();
    $this->get("/relatos/{$sighting->id}")->assertOk();

    $this->actingAs($this->moderator)
        ->post("/painel/relatos/{$sighting->id}/ajuste", ['message' => 'Tire a placa do carro da segunda foto.'])
        ->assertSessionHasNoErrors();

    expect($sighting->fresh())->status->value->toBe('changes_requested')->published_at->toBeNull();
    $this->getJson('/api/sightings')->assertJsonCount(0, 'data');
    auth()->logout();
    $this->get("/relatos/{$sighting->id}")->assertNotFound();
    Mail::assertQueued(SightingChangesRequestedMail::class, fn ($mail) => $mail->hasTo('maria@example.com')
        && $mail->url === route('report.edit', $sighting->id)
        && $mail->note === 'Tire a placa do carro da segunda foto.');
});

it('requires a message to request changes', function () {
    $sighting = Sighting::factory()->create();

    $this->actingAs($this->moderator)->post("/painel/relatos/{$sighting->id}/ajuste", ['message' => ''])->assertSessionHasErrors('message');
});

it('unpublishes with a note kept in the history', function () {
    $sighting = Sighting::factory()->approved()->create();

    $this->actingAs($this->moderator)
        ->post("/painel/relatos/{$sighting->id}/despublicar", ['note' => 'Ponto parece ser uma residência.'])
        ->assertSessionHasNoErrors();

    expect($sighting->fresh()->status->value)->toBe('pending');
    $this->getJson('/api/sightings')->assertJsonCount(0, 'data');
    $this->get("/painel/relatos/{$sighting->id}")->assertInertia(fn (Assert $page) => $page
        ->where('history.0.action', 'sighting.unpublished')
        ->where('history.0.context.note', 'Ponto parece ser uma residência.')
    );
});

it('refuses a decision the current status does not allow', function () {
    $sighting = Sighting::factory()->rejected()->create();

    $this->actingAs($this->moderator)->post("/painel/relatos/{$sighting->id}/aprovar")->assertSessionHasErrors('status');
    expect($sighting->fresh()->status->value)->toBe('rejected');
});

it('records every decision with author and values before and after', function () {
    $sighting = Sighting::factory()->create();

    $this->actingAs($this->moderator)->post("/painel/relatos/{$sighting->id}/aprovar");
    $this->post("/painel/relatos/{$sighting->id}/ajuste", ['message' => 'Conte também a direção do olhar.']);

    $logs = AuditLog::query()->where('subject_id', $sighting->id)->oldest('id')->get();
    expect($logs)->toHaveCount(2)
        ->and($logs[0]->actor_id)->toBe($this->moderator->id)
        ->and($logs[0]->action)->toBe('sighting.approved')
        ->and($logs[0]->before['status'])->toBe('pending')
        ->and($logs[0]->after['status'])->toBe('approved')
        ->and($logs[1]->after['moderationNote'])->toBe('Conte também a direção do olhar.');
});

it('refuses moderation to store operators and members', function () {
    $sighting = Sighting::factory()->create();

    $this->actingAs(Member::factory()->role('store')->create())->post("/painel/relatos/{$sighting->id}/aprovar")->assertForbidden();
    $this->actingAs($this->author)->post("/painel/relatos/{$sighting->id}/aprovar")->assertForbidden();
    expect($sighting->fresh()->status->value)->toBe('pending');
});

it('stores the approximate city found by the geocoder', function () {
    $sighting = Sighting::factory()->create(['approx_city' => null]);
    app()->instance(Geocoder::class, new class implements Geocoder
    {
        public function cityAt(float $lat, float $lng): ?string
        {
            return 'Lages, SC';
        }
    });

    LocateSighting::dispatchSync($sighting->id);

    expect($sighting->fresh()->approx_city)->toBe('Lages, SC');
});

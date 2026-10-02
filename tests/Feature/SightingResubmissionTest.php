<?php

use App\Jobs\LocateSighting;
use App\Jobs\ProcessSightingPhoto;
use App\Mail\SightingAwaitingModerationMail;
use App\Models\Member;
use App\Models\Sighting;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    Mail::fake();
    Queue::fake();
    Storage::fake('local');
    $this->author = Member::factory()->create(['nickname' => 'coruja']);
    $this->sighting = Sighting::factory()->for($this->author)->changesRequested()->create(['public_nickname' => 'coruja']);
});

function resubmitPayload(array $overrides = []): array
{
    return [
        'type' => 'object',
        'description' => 'Um disco prateado parado sobre a serra, sem barulho nenhum.',
        'observedDate' => now()->toDateString(),
        'timeRange' => 'night',
        'lat' => -27.81,
        'lng' => -50.31,
        'gaze' => 'N',
        'nickname' => 'coruja',
        'consent' => '1',
        'photos' => [],
        'keptPhotos' => [],
        ...$overrides,
    ];
}

it('opens the wizard with the report loaded and the tower note', function () {
    $this->actingAs($this->author)->get("/relatar/{$this->sighting->id}/editar")->assertInertia(fn (Assert $page) => $page
        ->component('Sightings/Report')
        ->where('editing.id', $this->sighting->id)
        ->where('editing.moderationNote', 'Tire o rosto da foto, por favor.')
    );
});

it('refuses another member', function () {
    $other = Member::factory()->create();

    $this->actingAs($other)->get("/relatar/{$this->sighting->id}/editar")->assertForbidden();
    $this->put("/relatar/{$this->sighting->id}", resubmitPayload())->assertForbidden();
});

it('sends the author back to the account when nothing waits for adjustment', function () {
    $approved = Sighting::factory()->for($this->author)->approved()->create();

    $this->actingAs($this->author)->get("/relatar/{$approved->id}/editar")->assertRedirect('/conta');
    $this->put("/relatar/{$approved->id}", resubmitPayload())->assertSessionHasErrors('status');
});

it('puts the report back in the queue as pending when resent', function () {
    $moderator = Member::factory()->role('moderator')->create();

    $this->actingAs($this->author)->put("/relatar/{$this->sighting->id}", resubmitPayload())->assertRedirect('/relatar/enviado');

    expect($this->sighting->fresh())
        ->status->value->toBe('pending')
        ->moderation_note->toBeNull()
        ->type->value->toBe('object')
        ->submitted_at->not->toBeNull();
    Mail::assertQueued(SightingAwaitingModerationMail::class, fn ($mail) => $mail->hasTo($moderator->email));
    Queue::assertPushed(LocateSighting::class);

    $this->actingAs($moderator)->get('/painel/relatos')->assertInertia(fn (Assert $page) => $page
        ->where('items.0.id', $this->sighting->id)
    );
});

it('keeps the photos the author kept and erases the removed ones', function () {
    $kept = processedPhoto($this->sighting);
    $removed = processedPhoto($this->sighting);

    $this->actingAs($this->author)
        ->put("/relatar/{$this->sighting->id}", resubmitPayload(['keptPhotos' => [$kept->id]]))
        ->assertRedirect('/relatar/enviado');

    expect($this->sighting->photos()->pluck('id')->all())->toBe([$kept->id]);
    Storage::disk('local')->assertExists("{$kept->path}-400.webp");
    Storage::disk('local')->assertMissing("{$removed->path}-400.webp");
    Queue::assertNotPushed(ProcessSightingPhoto::class);
});

it('refuses keeping a photo from another report', function () {
    $foreign = processedPhoto(Sighting::factory()->create());

    $this->actingAs($this->author)
        ->put("/relatar/{$this->sighting->id}", resubmitPayload(['keptPhotos' => [$foreign->id]]))
        ->assertSessionHasErrors('photos');
});

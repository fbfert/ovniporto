<?php

use App\Mail\SightingAwaitingModerationMail;
use App\Mail\SightingReceivedMail;
use App\Models\Member;
use App\Models\Sighting;
use App\Models\SightingPhoto;
use App\Models\SightingUpload;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Support\JpegWithExif;

beforeEach(function () {
    Storage::fake('local');
    Storage::fake('public');
    $this->member = Member::factory()->create(['nickname' => 'coruja']);
});

function reportPayload(array $overrides = []): array
{
    return [
        'type' => 'light',
        'description' => 'Uma luz verde parada sobre a serra, depois sumiu de uma vez.',
        'observedDate' => now()->toDateString(),
        'timeRange' => 'night',
        'lat' => -27.85,
        'lng' => -50.22,
        'gaze' => 'NE',
        'nickname' => 'coruja',
        'consent' => '1',
        'photos' => [],
        ...$overrides,
    ];
}

function uploadExifPhoto(): string
{
    $file = UploadedFile::fake()->createWithContent('ceu.jpg', JpegWithExif::make(1600, 1200));

    return test()->postJson('/relatar/fotos', ['photo' => $file])->assertCreated()->json('id');
}

it('asks guests to sign in before reporting', function () {
    $this->get('/relatar')->assertRedirect('/entrar');
});

it('opens the wizard for a member with a complete profile', function () {
    $this->actingAs($this->member)->get('/relatar')->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('Sightings/Report')
        ->where('nickname', 'coruja')
        ->where('limits.maxPhotos', 3)
    );
});

it('refuses a photo over 8 MB', function () {
    $this->actingAs($this->member)
        ->postJson('/relatar/fotos', ['photo' => UploadedFile::fake()->create('grande.jpg', 9 * 1024, 'image/jpeg')])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('photo');
});

it('keeps uploads on the private disk, out of any public URL', function () {
    $this->actingAs($this->member);
    $id = uploadExifPhoto();

    $path = SightingUpload::query()->findOrFail($id)->path;
    Storage::disk('local')->assertExists($path);
    Storage::disk('public')->assertMissing($path);
    $this->get('/storage/'.$path)->assertNotFound();
});

it('creates the report in analysis, with consent date, and queues both e-mails', function () {
    Mail::fake();
    $moderator = Member::factory()->role('moderator')->create();

    $this->actingAs($this->member)->post('/relatar', reportPayload())->assertRedirect('/relatar/enviado');

    $sighting = Sighting::query()->sole();
    expect($sighting->status->value)->toBe('pending')
        ->and($sighting->member_id)->toBe($this->member->id)
        ->and($sighting->public_nickname)->toBe('coruja')
        ->and($sighting->consent_given_at)->not->toBeNull()
        ->and($sighting->published_at)->toBeNull();
    Mail::assertQueued(SightingReceivedMail::class, fn ($mail) => $mail->hasTo($this->member->email));
    Mail::assertQueued(SightingAwaitingModerationMail::class, fn ($mail) => $mail->hasTo($moderator->email));
});

it('refuses a report without consent or outside the radius', function () {
    $this->actingAs($this->member);

    $this->post('/relatar', reportPayload(['consent' => null]))->assertSessionHasErrors('consent');
    $this->post('/relatar', reportPayload(['lat' => -23.55, 'lng' => -46.63]))->assertSessionHasErrors('point');

    expect(Sighting::query()->count())->toBe(0);
});

it('never keeps EXIF: the stored photos have no metadata and the original is gone', function () {
    $this->actingAs($this->member);
    $upload = uploadExifPhoto();
    $originalPath = SightingUpload::query()->findOrFail($upload)->path;

    $this->post('/relatar', reportPayload(['photos' => [$upload]]))->assertRedirect('/relatar/enviado');

    $photo = SightingPhoto::query()->sole();
    expect($photo->variants)->toBe([400, 800, 1600])
        ->and($photo->processed_at)->not->toBeNull();
    Storage::disk('local')->assertMissing($originalPath);
    expect(SightingUpload::query()->count())->toBe(0);

    foreach ($photo->variants as $width) {
        $bytes = Storage::disk('local')->get("{$photo->path}-{$width}.webp");
        expect($bytes)->not->toContain('Exif')->not->toContain(JpegWithExif::MAKE);
    }
});

it('does not let a member attach someone else\'s upload', function () {
    $this->actingAs(Member::factory()->create());
    $foreign = uploadExifPhoto();

    $this->actingAs($this->member)->post('/relatar', reportPayload(['photos' => [$foreign]]))->assertSessionHasErrors('photos');
});

it('serves a pending photo only through a signed URL, to its author or a moderator', function () {
    $this->actingAs($this->member);
    $this->post('/relatar', reportPayload(['photos' => [uploadExifPhoto()]]));
    $photo = SightingPhoto::query()->sole();
    $signed = URL::temporarySignedRoute('sighting.photo', now()->addMinutes(10), ['photo' => $photo->id, 'width' => 400]);

    $this->get("/fotos/relatos/{$photo->id}/400")->assertNotFound();
    $this->get($signed)->assertOk()->assertHeader('Content-Type', 'image/webp');
    $this->actingAs(Member::factory()->create())->get($signed)->assertNotFound();
    $this->actingAs(Member::factory()->role('moderator')->create())->get($signed)->assertOk();
});

it('serves an approved report\'s photo to anyone', function () {
    $this->actingAs($this->member);
    $this->post('/relatar', reportPayload(['photos' => [uploadExifPhoto()]]));
    Sighting::query()->update(['status' => 'approved', 'published_at' => now()]);
    $photo = SightingPhoto::query()->sole();

    auth()->logout();
    $this->get("/fotos/relatos/{$photo->id}/800")->assertOk();
});

it('prunes uploads no report claimed after 24 hours', function () {
    $this->actingAs($this->member);
    $id = uploadExifPhoto();
    $path = SightingUpload::query()->findOrFail($id)->path;
    SightingUpload::query()->whereKey($id)->update(['created_at' => now()->subHours(25)]);

    $this->artisan('sightings:prune-uploads')->assertSuccessful();

    expect(SightingUpload::query()->count())->toBe(0);
    Storage::disk('local')->assertMissing($path);
});

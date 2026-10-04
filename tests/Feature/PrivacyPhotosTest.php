<?php

use App\Infrastructure\Sightings\SightingPhotoUrls;
use App\Models\Member;
use App\Models\Sighting;
use App\Models\SightingPhoto;
use App\Models\SightingUpload;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\Support\JpegWithExif;

beforeEach(function () {
    Storage::fake('local');
    Storage::fake('public');
    Mail::fake();
    $this->author = Member::factory()->create(['nickname' => 'coruja']);
});

/** Every stored file on both disks that still carries any metadata signature. */
function filesWithMetadata(): array
{
    $found = [];
    foreach (['local', 'public'] as $disk) {
        foreach (Storage::disk($disk)->allFiles() as $path) {
            $bytes = (string) Storage::disk($disk)->get($path);
            foreach (JpegWithExif::SIGNATURES as $signature) {
                if (str_contains($bytes, $signature)) {
                    $found[] = "{$disk}:{$path} ({$signature})";
                }
            }
        }
    }

    return $found;
}

function geotaggedUpload(): string
{
    $file = UploadedFile::fake()->createWithContent('ceu.jpg', JpegWithExif::make(1600, 1200));

    return test()->postJson('/relatar/fotos', ['photo' => $file])->assertCreated()->json('id');
}

it('keeps no EXIF, XMP or IPTC in any stored file, from upload to approval', function () {
    expect(JpegWithExif::make())->toContain(JpegWithExif::TAKEN_AT)->toContain(JpegWithExif::IPTC_MARKER);
    $this->actingAs($this->author);

    $upload = geotaggedUpload();
    expect(Storage::disk('local')->allFiles())->toHaveCount(1)
        ->and(filesWithMetadata())->toBe([]);

    $this->post('/relatar', [
        'type' => 'light', 'description' => 'Uma luz verde parada sobre a serra, depois sumiu de uma vez.',
        'observedDate' => now()->toDateString(), 'timeRange' => 'night', 'lat' => -27.85, 'lng' => -50.22,
        'gaze' => 'NE', 'nickname' => 'coruja', 'consent' => '1', 'photos' => [$upload],
    ])->assertRedirect('/relatar/enviado');

    $photo = SightingPhoto::query()->sole();
    expect($photo->variants)->toBe([400, 800, 1600])
        ->and(Storage::disk('local')->allFiles())->toHaveCount(3)
        ->and(filesWithMetadata())->toBe([])
        ->and(SightingUpload::query()->count())->toBe(0);

    $moderator = Member::factory()->role('moderator')->create();
    $this->actingAs($moderator)->post('/painel/relatos/'.Sighting::query()->sole()->id.'/aprovar')->assertRedirect();

    expect(filesWithMetadata())->toBe([]);
});

it('stores the upload already upright, so no orientation tag is needed later', function () {
    $this->actingAs($this->author);
    $file = UploadedFile::fake()->createWithContent('deitada.jpg', JpegWithExif::make(1200, 900, orientation: 6));

    $id = $this->postJson('/relatar/fotos', ['photo' => $file])->assertCreated()->json('id');

    [$width, $height] = getimagesizefromstring((string) Storage::disk('local')->get(SightingUpload::query()->findOrFail($id)->path));
    expect([$width, $height])->toBe([900, 1200]);
});

it('refuses on upload a file the server cannot read, instead of storing it', function () {
    $this->actingAs($this->author);
    $file = UploadedFile::fake()->createWithContent('foto.heic', 'not-really-an-image');

    $this->postJson('/relatar/fotos', ['photo' => $file])->assertUnprocessable()->assertJsonValidationErrors('photo');

    expect(Storage::disk('local')->allFiles())->toBe([]);
});

/** A pending report with one processed photo, and a fresh 10-minute signed URL to it. */
function pendingPhoto(Member $author): array
{
    test()->actingAs($author);
    $upload = geotaggedUpload();
    test()->post('/relatar', [
        'type' => 'light', 'description' => 'Uma luz verde parada sobre a serra, depois sumiu de uma vez.',
        'observedDate' => now()->toDateString(), 'timeRange' => 'night', 'lat' => -27.85, 'lng' => -50.22,
        'gaze' => 'NE', 'nickname' => 'coruja', 'consent' => '1', 'photos' => [$upload],
    ]);
    $photo = SightingPhoto::query()->sole();

    return [$photo, SightingPhotoUrls::signed($photo, 400)];
}

it('answers 404 on the public route for a pending photo', function () {
    [$photo] = pendingPhoto($this->author);

    auth()->logout();
    $this->get("/fotos/relatos/{$photo->id}/400")->assertNotFound();
    $this->actingAs($this->author)->get("/fotos/relatos/{$photo->id}/400")->assertNotFound();
});

it('denies a signed URL issued 11 minutes ago, even to the author', function () {
    [, $signed] = pendingPhoto($this->author);
    $this->get($signed)->assertOk();

    $this->travel(11)->minutes();

    $this->get($signed)->assertNotFound();
});

it('opens a pending photo only to its author, moderators and admins', function () {
    [, $signed] = pendingPhoto($this->author);

    $this->actingAs(Member::factory()->role('moderator')->create())->get($signed)->assertOk();
    $this->actingAs(Member::factory()->role('admin')->create())->get($signed)->assertOk();
    $this->actingAs(Member::factory()->role('store')->create())->get($signed)->assertNotFound();
    $this->actingAs(Member::factory()->create())->get($signed)->assertNotFound();
    auth()->logout();
    $this->get($signed)->assertNotFound();
});

it('issues no signed URL to a member who is not the author', function () {
    [$photo] = pendingPhoto($this->author);

    $this->actingAs(Member::factory()->create())
        ->get('/relatos/'.$photo->sighting_id)
        ->assertNotFound()
        ->assertDontSee('signature=', false);
    $this->actingAs(Member::factory()->role('store')->create())
        ->get('/painel/relatos/'.$photo->sighting_id)
        ->assertForbidden();
});

it('issues the author 10-minute signed URLs on the report page', function () {
    [$photo] = pendingPhoto($this->author);

    $html = $this->actingAs($this->author)->get('/relatos/'.$photo->sighting_id)->assertOk()->getContent();

    preg_match('/expires=(\d+)/', html_entity_decode($html), $match);
    expect((int) $match[1])->toBe(now()->addMinutes(10)->getTimestamp());
});

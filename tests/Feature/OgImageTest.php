<?php

use App\Domain\Content\Contracts\OgImageRenderer;
use App\Infrastructure\Brand\GdOgImageRenderer;
use App\Models\Product;
use App\Models\Sighting;
use Database\Seeders\ProductSeeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->ogCache = sys_get_temp_dir().'/ovniporto-og-'.uniqid();
    $this->app->instance(OgImageRenderer::class, new GdOgImageRenderer($this->ogCache));
    Storage::fake('local');
    Storage::fake('public');
});

afterEach(fn () => File::deleteDirectory($this->ogCache));

/** A processed report photo: real WebP variants, like the image pipeline leaves them. */
function photographedSighting(array $attributes = [], array $rgb = [84, 201, 51]): Sighting
{
    $sighting = Sighting::factory()->create($attributes);
    $photo = $sighting->photos()->create(['path' => 'tmp', 'width' => 1600, 'height' => 1200, 'sort_order' => 0]);
    storeWebp("sightings/{$sighting->id}/{$photo->id}", $rgb);
    $photo->update(['path' => "sightings/{$sighting->id}/{$photo->id}", 'variants' => [400, 1600], 'processed_at' => now()]);

    return $sighting;
}

/** @param array{int, int, int} $rgb */
function storeWebp(string $base, array $rgb): void
{
    foreach ([400, 1600] as $width) {
        $image = imagecreatetruecolor($width, (int) ($width * 0.75));
        imagefill($image, 0, 0, (int) imagecolorallocate($image, ...$rgb));
        ob_start();
        imagewebp($image);
        Storage::disk('local')->put("{$base}-{$width}.webp", (string) ob_get_clean());
    }
}

function ogUrlOf(string $page): string
{
    preg_match('#<meta property="og:image" content="[^"]*(/og/[^"]+)">#', test()->get($page)->getContent(), $match);

    return $match[1] ?? '';
}

it('draws a 1200×630 preview for an approved report', function () {
    $sighting = photographedSighting();
    $sighting->update(['status' => 'approved', 'published_at' => now()]);

    $url = ogUrlOf("/relatos/{$sighting->id}");
    $response = $this->get($url)->assertOk()->assertHeader('Content-Type', 'image/jpeg');

    expect($url)->toMatch('#^/og/relato/\d+\.[a-f0-9]{10}\.jpg$#')
        ->and($response->headers->get('Cache-Control'))->toContain('immutable');
    [$width, $height] = getimagesize($response->baseResponse->getFile()->getPathname());
    expect([$width, $height])->toBe([1200, 630]);
});

it('returns 404 for the preview of a pending report', function () {
    $sighting = photographedSighting();

    $this->get("/og/relato/{$sighting->id}.0123456789.jpg")->assertNotFound();
});

it('returns 404 for the preview of an inactive product', function () {
    $this->seed(ProductSeeder::class);
    Product::query()->where('slug', 'adesivo-ovniporto')->update(['is_active' => false]);

    $this->get('/og/produto/adesivo-ovniporto.0123456789.jpg')->assertNotFound();
});

it('draws a new image when the first photo changes and sends old links to it', function () {
    $sighting = photographedSighting(rgb: [84, 201, 51]);
    $sighting->update(['status' => 'approved', 'published_at' => now()]);
    $before = ogUrlOf("/relatos/{$sighting->id}");
    $oldFile = $this->get($before)->assertOk()->baseResponse->getFile()->getPathname();
    $oldBytes = file_get_contents($oldFile);

    $photo = $sighting->photos()->first();
    storeWebp("sightings/{$sighting->id}/new-{$photo->id}", [252, 184, 2]);
    $photo->update(['path' => "sightings/{$sighting->id}/new-{$photo->id}"]);

    $after = ogUrlOf("/relatos/{$sighting->id}");
    $newFile = $this->get($after)->assertOk()->baseResponse->getFile()->getPathname();

    expect($after)->not->toBe($before)
        ->and(file_get_contents($newFile))->not->toBe($oldBytes)
        ->and(file_exists($oldFile))->toBeFalse();
    $this->get($before)->assertRedirect($after);
});

it('falls back to the drawn night scene when the content has no photo', function () {
    $this->seed(ProductSeeder::class);

    $url = ogUrlOf('/loja/adesivo-ovniporto');

    $this->get($url)->assertOk()->assertHeader('Content-Type', 'image/jpeg');
});

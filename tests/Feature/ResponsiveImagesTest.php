<?php

use App\Infrastructure\Images\GdImageProcessor;
use App\Infrastructure\Images\PublicImageLibrary;
use Illuminate\Support\Facades\Storage;

/** @return string JPEG bytes */
function photoBytes(int $width, int $height): string
{
    $image = imagecreatetruecolor($width, $height);
    imagefill($image, 0, 0, (int) imagecolorallocate($image, 20, 38, 68));
    ob_start();
    imagejpeg($image);

    return (string) ob_get_clean();
}

beforeEach(fn () => Storage::fake('public'));

it('writes AVIF and WebP in every width, the placeholder and the canonical file', function () {
    $path = (new PublicImageLibrary(new GdImageProcessor))->store(photoBytes(2000, 1500), 'products');
    $base = substr($path, 0, -5);

    expect($path)->toMatch('#^products/[0-9a-f-]{36}\.webp$#');
    foreach ([400, 800, 1200, 1600] as $width) {
        [$w] = getimagesizefromstring((string) Storage::disk('public')->get("{$base}-{$width}.webp"));
        expect($w)->toBe($width);
        Storage::disk('public')->assertExists("{$base}-{$width}.avif");
    }
    [$placeholder] = getimagesizefromstring((string) Storage::disk('public')->get("{$base}-20.webp"));
    expect($placeholder)->toBe(20)
        ->and(getimagesizefromstring((string) Storage::disk('public')->get($path))[0])->toBe(1600);
});

it('fills the larger widths at the original size, so every srcset entry exists', function () {
    $path = (new PublicImageLibrary(new GdImageProcessor))->store(photoBytes(600, 400), 'partners');
    $base = substr($path, 0, -5);

    expect(getimagesizefromstring((string) Storage::disk('public')->get("{$base}-1600.webp"))[0])->toBe(600)
        ->and(getimagesizefromstring((string) Storage::disk('public')->get("{$base}-400.webp"))[0])->toBe(400);
});

it('deletes every sibling with the image', function () {
    $library = new PublicImageLibrary(new GdImageProcessor);
    $library->delete($library->store(photoBytes(900, 600), 'diary'));

    expect(Storage::disk('public')->allFiles())->toBe([]);
});

it('backfills the siblings of an image stored before them', function () {
    Storage::disk('public')->put('site/0b4a8f2e-3c1d-4e5f-9a7b-1c2d3e4f5a6b.webp', (string) (function () {
        $image = imagecreatetruecolor(1600, 1000);
        ob_start();
        imagewebp($image);

        return ob_get_clean();
    })());

    $this->artisan('images:responsive')->expectsOutputToContain('1 imagem')->assertSuccessful();

    Storage::disk('public')->assertExists('site/0b4a8f2e-3c1d-4e5f-9a7b-1c2d3e4f5a6b-1200.avif');
    Storage::disk('public')->assertExists('site/0b4a8f2e-3c1d-4e5f-9a7b-1c2d3e4f5a6b-20.webp');
});

<?php

use Illuminate\Support\Facades\File;

function makeOriginPhoto(string $path, int $width, int $height): void
{
    $image = imagecreatetruecolor($width, $height);
    imagefill($image, 0, 0, (int) imagecolorallocate($image, 120, 90, 40));
    imagejpeg($image, $path);
}

beforeEach(function () {
    $this->dir = storage_path('framework/testing/origin-'.uniqid());
    File::ensureDirectoryExists("{$this->dir}/src");
    File::put("{$this->dir}/images.json", json_encode([
        ['slug' => 'cachi-aereo', 'original' => 'aereo.jpg'],
        ['slug' => 'atlas-st-paul', 'original' => 'st-paul.jpg'],
    ]));
});

afterEach(fn () => File::deleteDirectory($this->dir));

function importOrigin(object $test): object
{
    return $test->artisan('origin:images', [
        'source' => "{$test->dir}/src",
        '--credits' => "{$test->dir}/images.json",
        '--output' => "{$test->dir}/out",
        '--manifest' => "{$test->dir}/origin-images.json",
    ]);
}

it('builds the variants of every credited image without upscaling', function () {
    makeOriginPhoto("{$this->dir}/src/aereo.jpg", 900, 600);
    makeOriginPhoto("{$this->dir}/src/st-paul.jpg", 1700, 1000);

    importOrigin($this)->assertSuccessful();

    $manifest = json_decode(File::get("{$this->dir}/origin-images.json"), true);
    expect($manifest['cachi-aereo']['widths'])->toBe([400, 800])
        ->and($manifest['atlas-st-paul']['widths'])->toBe([400, 800, 1200, 1600])
        ->and(File::exists("{$this->dir}/out/cachi-aereo-800.avif"))->toBeTrue()
        ->and(File::exists("{$this->dir}/out/cachi-aereo-1200.webp"))->toBeFalse();
});

it('fails when a credited image is missing', function () {
    makeOriginPhoto("{$this->dir}/src/aereo.jpg", 900, 600);

    importOrigin($this)->assertFailed()->expectsOutputToContain('st-paul.jpg');
});

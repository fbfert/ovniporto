<?php

use Illuminate\Support\Facades\File;

beforeEach(function () {
    $this->dir = storage_path('framework/testing/seal-'.uniqid());
    File::ensureDirectoryExists($this->dir);
});

afterEach(fn () => File::deleteDirectory($this->dir));

it('cuts the round sticker out with a transparent outside', function () {
    $art = imagecreatetruecolor(400, 400);
    imagefill($art, 0, 0, (int) imagecolorallocate($art, 40, 40, 40));
    imagefilledellipse($art, 200, 200, 300, 300, (int) imagecolorallocate($art, 84, 201, 51));
    imagepng($art, "{$this->dir}/sticker.png");

    $this->artisan('brand:seal', [
        'source' => "{$this->dir}/sticker.png",
        '--cx' => 200, '--cy' => 200, '--r' => 150,
        '--output' => "{$this->dir}/out",
    ])->assertSuccessful();

    $seal = imagecreatefromwebp("{$this->dir}/out/seal-256.webp");
    $corner = imagecolorsforindex($seal, imagecolorat($seal, 2, 2));
    $center = imagecolorsforindex($seal, imagecolorat($seal, 128, 128));

    expect($corner['alpha'])->toBe(127)
        ->and($center['alpha'])->toBe(0)
        ->and($center['green'])->toBeGreaterThan(150)
        ->and(File::exists("{$this->dir}/out/seal-512.webp"))->toBeFalse()
        ->and(File::exists("{$this->dir}/out/seal-128.avif"))->toBeTrue();
});

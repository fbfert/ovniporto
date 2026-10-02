<?php

use Illuminate\Support\Facades\File;

function makeIllustration(string $path, int $width, int $height): void
{
    $image = imagecreatetruecolor($width, $height);
    imagefill($image, 0, 0, (int) imagecolorallocate($image, 20, 38, 68));
    imagepng($image, $path);
}

beforeEach(function () {
    $this->dir = storage_path('framework/testing/concept-'.uniqid());
    File::ensureDirectoryExists("{$this->dir}/src");
});

afterEach(fn () => File::deleteDirectory($this->dir));

it('builds responsive derivatives and a manifest without upscaling', function () {
    makeIllustration("{$this->dir}/src/Torre de Controle.png", 1000, 1250);

    $this->artisan('concept:import', [
        'source' => "{$this->dir}/src",
        '--output' => "{$this->dir}/out",
        '--manifest' => "{$this->dir}/concept.json",
    ])->assertSuccessful();

    $manifest = json_decode(File::get("{$this->dir}/concept.json"), true);

    expect($manifest['tower'])->toMatchArray(['width' => 1000, 'height' => 1250, 'widths' => [400, 800]])
        ->and($manifest['tower']['lqip'])->toStartWith('data:image/webp;base64,')
        ->and(File::exists("{$this->dir}/out/tower-800.avif"))->toBeTrue()
        ->and(File::exists("{$this->dir}/out/tower-800.webp"))->toBeTrue()
        ->and(File::exists("{$this->dir}/out/tower-1200.avif"))->toBeFalse()
        ->and(File::exists("{$this->dir}/out/tower.jpg"))->toBeTrue();
});

it('fails when the source has none of the expected illustrations', function () {
    $this->artisan('concept:import', [
        'source' => "{$this->dir}/src",
        '--output' => "{$this->dir}/out",
        '--manifest' => "{$this->dir}/concept.json",
    ])->assertFailed();
});

<?php

use App\Domain\Sightings\UnsupportedImage;
use App\Infrastructure\Images\GdImageProcessor;
use App\Infrastructure\Images\JpegOrientation;
use Tests\Support\JpegWithExif;

it('reads the orientation tag from the EXIF block', function () {
    expect(JpegOrientation::read(JpegWithExif::make(orientation: 6)))->toBe(6)
        ->and(JpegOrientation::read(JpegWithExif::make()))->toBe(1)
        ->and(JpegOrientation::read('not a jpeg'))->toBe(1);
});

it('writes WebP variants with none of the original metadata', function () {
    $original = JpegWithExif::make(1200, 900);
    expect($original)->toContain(JpegWithExif::MAKE)->toContain('Exif');

    $image = (new GdImageProcessor)->process($original, [400, 800, 1600]);

    expect([$image->width, $image->height])->toBe([1200, 900])
        ->and(array_keys($image->variants))->toBe([400, 800]); // never upscaled past 1200
    foreach ($image->variants as $webp) {
        expect(substr($webp, 0, 4))->toBe('RIFF')
            ->and(substr($webp, 8, 4))->toBe('WEBP')
            ->and($webp)->not->toContain('Exif')
            ->not->toContain('EXIF')
            ->not->toContain('XMP')
            ->not->toContain(JpegWithExif::MAKE);
    }
});

it('turns a sideways phone photo upright before dropping the tag', function () {
    $image = (new GdImageProcessor)->process(JpegWithExif::make(1200, 900, orientation: 6), [400]);

    expect([$image->width, $image->height])->toBe([900, 1200]);
});

it('refuses a file it cannot decode', function () {
    (new GdImageProcessor)->process('definitely not an image', [400]);
})->throws(UnsupportedImage::class);

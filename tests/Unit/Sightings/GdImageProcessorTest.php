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
        // Never upscaled: 1600 becomes the photo's own 1200, kept once.
        ->and(array_keys($image->variants))->toBe([400, 800, 1200]);
    foreach ($image->variants as $webp) {
        expect(substr($webp, 0, 4))->toBe('RIFF')
            ->and(substr($webp, 8, 4))->toBe('WEBP');
    }
    foreach ([...$image->variants, ...$image->avif, $image->placeholder] as $encoded) {
        foreach (JpegWithExif::SIGNATURES as $signature) {
            expect($encoded)->not->toContain($signature);
        }
    }
    expect(array_keys($image->avif))->toBe(GdImageProcessor::canEncodeAvif() ? [400, 800, 1200] : []);
});

it('turns a sideways phone photo upright before dropping the tag', function () {
    $image = (new GdImageProcessor)->process(JpegWithExif::make(1200, 900, orientation: 6), [400]);

    expect([$image->width, $image->height])->toBe([900, 1200]);
});

it('refuses a file it cannot decode', function () {
    (new GdImageProcessor)->process('definitely not an image', [400]);
})->throws(UnsupportedImage::class);

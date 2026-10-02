<?php

use App\Domain\Map\Contracts\Geocoder;
use App\Infrastructure\Geo\CachedGeocoder;
use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\Repository;

function countingGeocoder(?string $answer): Geocoder
{
    return new class($answer) implements Geocoder
    {
        public int $calls = 0;

        public function __construct(private ?string $answer) {}

        public function cityAt(float $lat, float $lng): ?string
        {
            $this->calls++;

            return $this->answer;
        }
    };
}

it('asks the service once for points about a kilometre apart', function () {
    $inner = countingGeocoder('Lages, SC');
    $geocoder = new CachedGeocoder($inner, new Repository(new ArrayStore));

    expect($geocoder->cityAt(-27.8161, -50.3262))->toBe('Lages, SC')
        ->and($geocoder->cityAt(-27.8188, -50.3271))->toBe('Lages, SC')
        ->and($inner->calls)->toBe(1);
});

it('asks again for a point in another town', function () {
    $inner = countingGeocoder('Lages, SC');
    $geocoder = new CachedGeocoder($inner, new Repository(new ArrayStore));

    $geocoder->cityAt(-27.81, -50.32);
    $geocoder->cityAt(-28.29, -49.93);

    expect($inner->calls)->toBe(2);
});

it('remembers a miss too, without turning it into a city', function () {
    $inner = countingGeocoder(null);
    $geocoder = new CachedGeocoder($inner, new Repository(new ArrayStore));

    expect($geocoder->cityAt(-27.81, -50.32))->toBeNull()
        ->and($geocoder->cityAt(-27.81, -50.32))->toBeNull()
        ->and($inner->calls)->toBe(1);
});

<?php

namespace App\Jobs;

use App\Domain\Map\Contracts\Geocoder;
use App\Domain\Sightings\Contracts\SightingWriteRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\RateLimited;

/** Finds the town around a report's point for the moderation queue. Never shown publicly. */
class LocateSighting implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    public function __construct(public readonly int $sightingId) {}

    /** @return list<object> */
    public function middleware(): array
    {
        return [new RateLimited('geocoder')];
    }

    public function handle(SightingWriteRepository $sightings, Geocoder $geocoder): void
    {
        $point = $sightings->pointOf($this->sightingId);
        if ($point === null) {
            return;
        }

        $sightings->setApproxCity($this->sightingId, $geocoder->cityAt($point['lat'], $point['lng']));
    }
}

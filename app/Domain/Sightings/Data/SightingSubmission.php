<?php

namespace App\Domain\Sightings\Data;

use App\Domain\Sightings\GazeDirection;
use App\Domain\Sightings\SightingType;
use App\Domain\Sightings\TimeRange;
use DateTimeImmutable;

/** A report as the member sends it from the 4-step wizard. */
final readonly class SightingSubmission
{
    /** @param list<string> $uploadIds photos already uploaded to the temporary area, in display order */
    public function __construct(
        public SightingType $type,
        public string $description,
        public DateTimeImmutable $observedDate,
        public ?TimeRange $timeRange,
        public ?string $exactTime,
        public float $lat,
        public float $lng,
        public ?GazeDirection $gaze,
        public string $nickname,
        public bool $consent,
        public array $uploadIds,
    ) {}
}

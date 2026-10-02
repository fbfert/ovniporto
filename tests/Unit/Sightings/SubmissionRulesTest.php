<?php

use App\Domain\Sightings\Data\SightingSubmission;
use App\Domain\Sightings\InvalidSubmission;
use App\Domain\Sightings\SightingType;
use App\Domain\Sightings\SubmissionRules;
use App\Domain\Sightings\TimeRange;

function submission(array $overrides = []): SightingSubmission
{
    $values = [
        'type' => SightingType::Light,
        'description' => 'Uma luz verde parada sobre a serra, depois sumiu de uma vez.',
        'observedDate' => new DateTimeImmutable('2026-09-30'),
        'timeRange' => TimeRange::Night,
        'exactTime' => null,
        'lat' => -27.85,
        'lng' => -50.22,
        'gaze' => null,
        'nickname' => 'coruja',
        'consent' => true,
        'uploadIds' => [],
        ...$overrides,
    ];

    return new SightingSubmission(...$values);
}

$today = new DateTimeImmutable('2026-10-02');

it('accepts a complete report', function () use ($today) {
    SubmissionRules::validate(submission(), $today);
})->throwsNoExceptions();

it('refuses a report without consent', function () use ($today) {
    SubmissionRules::validate(submission(['consent' => false]), $today);
})->throws(InvalidSubmission::class, 'autorização');

it('refuses a point farther than 300 km from Lages', function () use ($today) {
    // São Paulo: about 480 km away
    SubmissionRules::validate(submission(['lat' => -23.55, 'lng' => -46.63]), $today);
})->throws(InvalidSubmission::class, '300 km');

it('refuses more than 3 photos', function () use ($today) {
    SubmissionRules::validate(submission(['uploadIds' => ['a', 'b', 'c', 'd']]), $today);
})->throws(InvalidSubmission::class, '3 fotos');

it('wants a time range or an exact time, never both nor neither', function (?TimeRange $range, ?string $time) use ($today) {
    SubmissionRules::validate(submission(['timeRange' => $range, 'exactTime' => $time]), $today);
})->with([
    'both' => [TimeRange::Night, '22:10'],
    'neither' => [null, null],
])->throws(InvalidSubmission::class);

it('refuses a date in the future', function () use ($today) {
    SubmissionRules::validate(submission(['observedDate' => new DateTimeImmutable('2026-10-03')]), $today);
})->throws(InvalidSubmission::class, 'futuro');

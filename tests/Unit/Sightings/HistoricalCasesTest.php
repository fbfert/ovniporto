<?php

use App\Application\Sightings\UseCases\GetHistoricalCase;
use App\Application\Sightings\UseCases\ListHistoricalCases;
use Tests\Support\FakeHistoricalCaseLibrary;

it('groups the cases by region in order, skipping empty regions, and maps every case', function () {
    $list = (new ListHistoricalCases(new FakeHistoricalCaseLibrary))->execute();

    expect(array_column($list['groups'], 'region'))->toBe(['sc', 'mundo'])
        ->and(array_column($list['groups'][0]['cases'], 'slug'))->toBe(['um', 'dois'])
        ->and($list['groups'][0]['cases'][0])->not->toHaveKey('sources')
        ->and($list['pins'])->toHaveCount(3)
        ->and($list['pins'][2])->toBe(['slug' => 'tres', 'title' => 'Caso tres', 'date' => '01/01/2000', 'lat' => 43.0, 'lng' => -48.0]);
});

it('resolves a case with its region, position and neighbours', function () {
    $page = (new GetHistoricalCase(new FakeHistoricalCaseLibrary))->execute('dois');

    expect($page['case']['slug'])->toBe('dois')
        ->and($page['region'])->toBe('Santa Catarina')
        ->and($page['previous'])->toBe(['slug' => 'um', 'title' => 'Caso um'])
        ->and($page['next'])->toBe(['slug' => 'tres', 'title' => 'Caso tres'])
        ->and([$page['position'], $page['total']])->toBe([2, 3]);
});

it('has no neighbour past the ends and returns null for an unknown case', function () {
    $useCase = new GetHistoricalCase(new FakeHistoricalCaseLibrary);

    expect($useCase->execute('um')['previous'])->toBeNull()
        ->and($useCase->execute('tres')['next'])->toBeNull()
        ->and($useCase->execute('nao-existe'))->toBeNull();
});

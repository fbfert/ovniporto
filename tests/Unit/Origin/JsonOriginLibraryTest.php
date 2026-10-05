<?php

use App\Domain\Origin\InvalidOriginData;
use App\Infrastructure\Origin\JsonOriginLibrary;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->dir = storage_path('framework/testing/origin-data-'.uniqid());
    File::ensureDirectoryExists($this->dir);
});

afterEach(fn () => File::deleteDirectory($this->dir));

function writeAtlas(string $dir, string $name = 'St. Paul UFO Landing Pad', string $seal = 'A/B'): void
{
    File::put("{$dir}/atlas.json", json_encode([
        'title' => 'Atlas', 'grades' => [], 'comparativeTimeline' => [], 'candidates' => [], 'sources' => [],
        'cases' => [['slug' => 'st-paul', 'number' => 1, 'name' => $name, 'country' => 'Canadá', 'seal' => $seal, 'facts' => [], 'sources' => [], 'openQuestions' => []]],
    ]));
}

it('reads the real versioned files', function () {
    $library = new JsonOriginLibrary(resource_path('content/origin'));

    expect($library->caseSlugs())->toHaveCount(12)->toContain('st-paul', 'cachi', 'lages')
        ->and($library->cachi()['chapters'])->toHaveCount(14)
        ->and($library->credits())->toHaveKey('cachi-aereo');
});

it('explains which field is missing in a malformed file', function () {
    File::put("{$this->dir}/atlas.json", json_encode(['title' => 'Atlas', 'grades' => [], 'comparativeTimeline' => [], 'candidates' => [], 'sources' => [], 'cases' => [['slug' => 'x']]]));

    (new JsonOriginLibrary($this->dir))->atlas();
})->throws(InvalidOriginData::class, 'atlas.cases[0] sem o campo: number');

it('rejects an invalid confidence seal and broken JSON', function () {
    writeAtlas($this->dir, seal: 'Z');
    expect(fn () => (new JsonOriginLibrary($this->dir))->atlas())->toThrow(InvalidOriginData::class, 'Z');

    File::put("{$this->dir}/cachi.json", '{ nope');
    expect(fn () => (new JsonOriginLibrary($this->dir))->cachi())->toThrow(InvalidOriginData::class, 'cachi.json não é um JSON válido');
});

it('picks up an edited file instead of serving the cached copy', function () {
    writeAtlas($this->dir);
    expect((new JsonOriginLibrary($this->dir))->atlas()['cases'][0]['name'])->toBe('St. Paul UFO Landing Pad');

    writeAtlas($this->dir, 'St. Paul (revisado)');
    touch("{$this->dir}/atlas.json", time() + 5);
    clearstatcache();

    expect((new JsonOriginLibrary($this->dir))->atlas()['cases'][0]['name'])->toBe('St. Paul (revisado)');
});

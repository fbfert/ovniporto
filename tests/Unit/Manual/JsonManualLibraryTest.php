<?php

use App\Domain\Manual\InvalidManualData;
use App\Infrastructure\Content\CommonMarkRenderer;
use App\Infrastructure\Manual\JsonManualLibrary;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->dir = storage_path('framework/testing/manual-'.uniqid());
    File::ensureDirectoryExists($this->dir);
});

afterEach(fn () => File::deleteDirectory($this->dir));

/** @param array<string, mixed> $overrides */
function manualChapter(array $overrides = []): array
{
    return array_replace([
        'slug' => 'relatos', 'area' => 'relatos', 'group' => 'community', 'order' => 10,
        'title' => 'Relatos', 'summary' => 'Moderar.', 'reviewedAt' => '2026-10-07', 'routes' => ['panel.sightings'],
        'map' => ['label' => 'Relatos', 'children' => [
            ['label' => 'Fila', 'kind' => 'screen', 'section' => 'fila', 'children' => [
                ['label' => 'Aprovar', 'kind' => 'action', 'section' => 'aprovar'],
            ]],
        ]],
        'sections' => [
            ['id' => 'fila', 'title' => 'A fila', 'screen' => 'panel.sightings', 'body' => 'A **fila**.'],
            ['id' => 'aprovar', 'title' => 'Aprovar', 'body' => 'Publica o relato.'],
        ],
    ], $overrides);
}

function writeChapter(string $dir, array $chapter): void
{
    File::put("{$dir}/{$chapter['slug']}.json", json_encode($chapter));
}

function manualLibrary(string $dir): JsonManualLibrary
{
    return new JsonManualLibrary($dir, new CommonMarkRenderer);
}

it('reads a chapter and renders its sections as HTML', function () {
    writeChapter($this->dir, manualChapter());

    $chapter = manualLibrary($this->dir)->chapters()[0];

    expect($chapter['slug'])->toBe('relatos')
        ->and($chapter['sections'][0]['html'])->toBe('<p>A <strong>fila</strong>.</p>')
        ->and($chapter['sections'][0]['screens'])->toBe(['panel.sightings'])
        ->and($chapter['sections'][1]['screens'])->toBe([]);
});

it('lets a section explain several screens, and refuses anything else as screen', function () {
    $chapter = manualChapter();
    $chapter['sections'][0]['screen'] = ['panel.diary.create', 'panel.diary.edit'];
    writeChapter($this->dir, $chapter);
    expect(manualLibrary($this->dir)->chapters()[0]['sections'][0]['screens'])->toBe(['panel.diary.create', 'panel.diary.edit']);

    $chapter['sections'][0]['screen'] = ['panel.diary' => 1];
    writeChapter($this->dir, $chapter);
    touch("{$this->dir}/relatos.json", time() + 5);
    expect(fn () => manualLibrary($this->dir)->chapters())->toThrow(InvalidManualData::class, 'screen deve ser um nome de rota ou uma lista de nomes');
});

it('orders chapters by group, then by order', function () {
    writeChapter($this->dir, manualChapter(['slug' => 'glossario', 'area' => null, 'group' => 'reference', 'order' => 10]));
    writeChapter($this->dir, manualChapter(['slug' => 'membros', 'order' => 20]));
    writeChapter($this->dir, manualChapter(['slug' => 'papeis', 'area' => null, 'group' => 'start', 'order' => 20]));
    writeChapter($this->dir, manualChapter());

    expect(array_column(manualLibrary($this->dir)->chapters(), 'slug'))->toBe(['papeis', 'relatos', 'membros', 'glossario']);
});

it('names chapter and node when a map node points to a missing section', function () {
    $chapter = manualChapter();
    $chapter['map']['children'][0]['children'][0]['section'] = 'rejeitar';
    writeChapter($this->dir, $chapter);

    manualLibrary($this->dir)->chapters();
})->throws(InvalidManualData::class, 'relatos: o nó "Aprovar" aponta para a seção inexistente "rejeitar"');

it('refuses malformed chapters with a clear message', function (array $overrides, string $message) {
    writeChapter($this->dir, manualChapter($overrides));

    expect(fn () => manualLibrary($this->dir)->chapters())->toThrow(InvalidManualData::class, $message);
})->with([
    'unknown area' => [['area' => 'cozinha'], 'área desconhecida: cozinha'],
    'unknown group' => [['group' => 'extra'], 'grupo desconhecido: extra'],
    'bad review date' => [['reviewedAt' => '07/10/2026'], 'reviewedAt deve ser uma data'],
    'repeated section' => [['sections' => [['id' => 'fila', 'title' => 'A', 'body' => ''], ['id' => 'fila', 'title' => 'B', 'body' => '']], 'map' => ['label' => 'R', 'children' => []]], 'seção repetida: fila'],
    'no sections' => [['sections' => []], 'sections deve ser uma lista não vazia'],
    'empty title' => [['title' => null], 'o campo title não pode ficar vazio'],
]);

it('names the missing field', function () {
    $chapter = manualChapter();
    unset($chapter['routes']);
    writeChapter($this->dir, $chapter);

    manualLibrary($this->dir)->chapters();
})->throws(InvalidManualData::class, 'relatos sem o campo: routes');

it('refuses a node without a known kind and a third level', function () {
    $chapter = manualChapter();
    $chapter['map']['children'][0]['kind'] = 'tela';
    writeChapter($this->dir, $chapter);
    expect(fn () => manualLibrary($this->dir)->chapters())->toThrow(InvalidManualData::class, 'o nó "Fila" tem tipo desconhecido: tela');

    $chapter = manualChapter();
    $chapter['map']['children'][0]['children'][0]['children'] = [['label' => 'Mais', 'kind' => 'action']];
    writeChapter($this->dir, $chapter);
    expect(fn () => manualLibrary($this->dir)->chapters())->toThrow(InvalidManualData::class, 'o nó "Aprovar" passa de dois níveis');
});

it('keeps the radial map within its branch limits', function () {
    $branch = ['label' => 'Ramo', 'kind' => 'screen', 'children' => []];
    writeChapter($this->dir, manualChapter(['map' => ['label' => 'Relatos', 'children' => array_fill(0, JsonManualLibrary::MAX_BRANCHES + 1, $branch)]]));

    manualLibrary($this->dir)->chapters();
})->throws(InvalidManualData::class, 'tem 8 ramos (máximo 7)');

it('refuses a slug that differs from the file name and broken JSON', function () {
    File::put("{$this->dir}/relatos.json", json_encode(manualChapter(['slug' => 'outro'])));
    expect(fn () => manualLibrary($this->dir)->chapters())->toThrow(InvalidManualData::class, 'o campo slug deve ser igual ao nome do arquivo');

    File::put("{$this->dir}/relatos.json", '{ nope');
    expect(fn () => manualLibrary($this->dir)->chapters())->toThrow(InvalidManualData::class, 'relatos.json não é um JSON válido');
});

it('picks up an edited chapter instead of serving the cached copy', function () {
    writeChapter($this->dir, manualChapter());
    expect(manualLibrary($this->dir)->chapters()[0]['title'])->toBe('Relatos');

    writeChapter($this->dir, manualChapter(['title' => 'Moderação']));
    touch("{$this->dir}/relatos.json", time() + 5);

    expect(manualLibrary($this->dir)->chapters()[0]['title'])->toBe('Moderação');
});

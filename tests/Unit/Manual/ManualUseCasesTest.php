<?php

use App\Application\Manual\UseCases\ListManualChapters;
use App\Application\Manual\UseCases\ShowManualChapter;
use App\Domain\Manual\Contracts\ManualLibrary;
use App\Domain\Members\MemberRole;

function fakeManual(): ManualLibrary
{
    $chapter = fn (string $slug, ?string $area, string $group) => [
        'slug' => $slug, 'area' => $area, 'group' => $group, 'order' => 10, 'title' => ucfirst($slug), 'summary' => "Sobre {$slug}.",
        'reviewedAt' => '2026-10-07', 'routes' => [], 'map' => ['label' => $slug, 'children' => []],
        'sections' => [['id' => 'a', 'title' => 'A', 'screens' => ['panel'], 'html' => '<p>a</p>']],
    ];

    return new class([$chapter('papeis', null, 'start'), $chapter('relatos', 'relatos', 'community'), $chapter('pedidos', 'pedidos', 'store'), $chapter('auditoria', 'auditoria', 'backstage')]) implements ManualLibrary
    {
        public function __construct(private array $chapters) {}

        public function chapters(): array
        {
            return $this->chapters;
        }
    };
}

it('lists the general chapters and the chapters of the areas each role opens', function (MemberRole $role, array $slugs) {
    expect(array_column((new ListManualChapters(fakeManual()))->execute($role), 'slug'))->toBe($slugs);
})->with([
    'admin' => [MemberRole::Admin, ['papeis', 'relatos', 'pedidos', 'auditoria']],
    'moderator' => [MemberRole::Moderator, ['papeis', 'relatos']],
    'store' => [MemberRole::Store, ['papeis', 'pedidos']],
    'member' => [MemberRole::Member, []],
]);

it('lists chapters without their bodies', function () {
    expect((new ListManualChapters(fakeManual()))->execute(MemberRole::Admin)[0])
        ->toBe(['slug' => 'papeis', 'group' => 'start', 'title' => 'Papeis', 'summary' => 'Sobre papeis.', 'reviewedAt' => '2026-10-07']);
});

it('shows a chapter with its neighbours among the readable ones', function () {
    $library = fakeManual();
    $result = (new ShowManualChapter($library, new ListManualChapters($library)))->execute(MemberRole::Store, 'pedidos');

    expect($result['chapter']['slug'])->toBe('pedidos')
        ->and($result['chapter']['sections'])->toBe([['id' => 'a', 'title' => 'A', 'html' => '<p>a</p>']])
        ->and($result['previous'])->toBe(['slug' => 'papeis', 'title' => 'Papeis'])
        ->and($result['next'])->toBeNull();
});

it('reports a chapter of a closed area, or an unknown one, as missing', function (MemberRole $role, string $slug) {
    $library = fakeManual();

    expect((new ShowManualChapter($library, new ListManualChapters($library)))->execute($role, $slug))->toBeNull();
})->with([
    'moderator reading orders' => [MemberRole::Moderator, 'pedidos'],
    'store reading the audit' => [MemberRole::Store, 'auditoria'],
    'unknown chapter' => [MemberRole::Admin, 'cozinha'],
]);

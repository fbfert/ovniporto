<?php

use App\Application\Content\UseCases\GetLegalPage;
use App\Application\Content\UseCases\RenderContentBlocks;
use App\Domain\Privacy\Contracts\PrivacyPractices;
use App\Infrastructure\Content\CommonMarkRenderer;
use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\Repository;

function legalPage(array $stored): GetLegalPage
{
    $blocks = contentRepository($stored);
    $practices = new class implements PrivacyPractices
    {
        public function title(): string
        {
            return 'O que fazemos na prática';
        }

        public function all(): array
        {
            return ['Fotos sem metadados.'];
        }
    };

    return new GetLegalPage($blocks, new RenderContentBlocks($blocks, new CommonMarkRenderer, new Repository(new ArrayStore)), $practices);
}

it('builds the index from the level-2 headings and anchors them', function () {
    $page = legalPage(['terms_body' => "## Uso do site\ntexto\n\n## Loja\ntexto"])->execute('terms');

    expect($page['toc'])->toBe([
        ['id' => 'uso-do-site', 'title' => 'Uso do site'],
        ['id' => 'loja', 'title' => 'Loja'],
    ])->and($page['html'])->toContain('<h2 id="uso-do-site">');
});

it('opens the privacy page with the practices, first in the index', function () {
    $page = legalPage(['privacy_body' => "## Dados coletados\ntexto"])->execute('privacy');

    expect($page['practices'])->toBe(['title' => 'O que fazemos na prática', 'items' => ['Fotos sem metadados.']])
        ->and($page['toc'])->toBe([
            ['id' => GetLegalPage::PRACTICES_ANCHOR, 'title' => 'O que fazemos na prática'],
            ['id' => 'dados-coletados', 'title' => 'Dados coletados'],
        ]);
});

it('keeps the practices off the terms page', function () {
    expect(legalPage(['terms_body' => '## Uso'])->execute('terms')['practices'])->toBeNull();
});

it('stays a draft until the final flag is set', function () {
    expect(legalPage(['terms_body' => '## Uso'])->execute('terms')['draft'])->toBeTrue()
        ->and(legalPage(['terms_body' => '## Uso', 'terms_final' => '1'])->execute('terms')['draft'])->toBeFalse();
});

it('rejects unknown pages', function () {
    legalPage([])->execute('cookies');
})->throws(InvalidArgumentException::class);

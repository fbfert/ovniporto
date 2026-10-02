<?php

use App\Application\Content\UseCases\GetLegalPage;
use App\Application\Content\UseCases\RenderContentBlocks;
use App\Infrastructure\Content\CommonMarkRenderer;
use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\Repository;

function legalPage(array $stored): GetLegalPage
{
    $blocks = contentRepository($stored);

    return new GetLegalPage($blocks, new RenderContentBlocks($blocks, new CommonMarkRenderer, new Repository(new ArrayStore)));
}

it('builds the index from the level-2 headings and anchors them', function () {
    $page = legalPage(['privacy_body' => "## Dados coletados\ntexto\n\n## Direitos do titular\ntexto"])->execute('privacy');

    expect($page['toc'])->toBe([
        ['id' => 'dados-coletados', 'title' => 'Dados coletados'],
        ['id' => 'direitos-do-titular', 'title' => 'Direitos do titular'],
    ])->and($page['html'])->toContain('<h2 id="dados-coletados">');
});

it('stays a draft until the final flag is set', function () {
    expect(legalPage(['terms_body' => '## Uso'])->execute('terms')['draft'])->toBeTrue()
        ->and(legalPage(['terms_body' => '## Uso', 'terms_final' => '1'])->execute('terms')['draft'])->toBeFalse();
});

it('rejects unknown pages', function () {
    legalPage([])->execute('cookies');
})->throws(InvalidArgumentException::class);

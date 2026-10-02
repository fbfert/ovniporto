<?php

use App\Application\Content\UseCases\RenderContentBlocks;
use App\Infrastructure\Content\CommonMarkRenderer;
use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\Repository;

function renderBlocks(array $stored): RenderContentBlocks
{
    return new RenderContentBlocks(contentRepository($stored), new CommonMarkRenderer, new Repository(new ArrayStore));
}

it('returns null for an empty block instead of an error', function () {
    $html = renderBlocks(['legend_body' => "  \n "])->execute(['legend_body', 'missing']);

    expect($html)->toBe(['legend_body' => null, 'missing' => null]);
});

it('renders markdown and strips raw HTML and unsafe links', function () {
    $html = renderBlocks([
        'legend_body' => "Era um **carro amarelo**.\n\n<script>alert(1)</script>\n\n[clique](javascript:alert(1))",
    ])->execute(['legend_body'])['legend_body'];

    expect($html)->toContain('<strong>carro amarelo</strong>')
        ->not->toContain('<script>')
        ->not->toContain('javascript:');
});

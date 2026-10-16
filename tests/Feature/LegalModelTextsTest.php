<?php

use App\Models\ContentBlock;
use Inertia\Testing\AssertableInertia as Assert;

function runLegalModelMigration(): void
{
    (require database_path('migrations/2026_10_16_000100_fill_legal_model_texts.php'))->up();
}

it('replaces the topic skeleton with the model texts and keeps the draft notice', function () {
    ContentBlock::query()->where('key', 'privacy_body')->update(['value' => "## Dados coletados\n_Tópico a ser redigido pelo encarregado._"]);
    ContentBlock::query()->where('key', 'terms_body')->update(['value' => '']);
    runLegalModelMigration();

    $this->get('/privacidade')->assertInertia(fn (Assert $page) => $page
        ->where('draft', true)
        ->where('html', fn (string $html) => str_contains($html, 'Bases legais') && ! str_contains($html, 'a ser redigido'))
        ->etc()
    );
    $this->get('/termos')->assertInertia(fn (Assert $page) => $page
        ->where('draft', true)
        ->where('html', fn (string $html) => str_contains($html, 'Um relato é um relato'))
        ->etc()
    );
});

it('refreshes a model text that still has blanks to fill', function () {
    ContentBlock::query()->where('key', 'privacy_body')->update(['value' => "## Contato do encarregado\n**[A preencher: nome do encarregado]**"]);
    (require database_path('migrations/2026_10_16_000200_refresh_legal_model_texts.php'))->up();

    expect(ContentBlock::query()->where('key', 'privacy_body')->value('value'))
        ->toContain('Felipe Boeck Fert')
        ->not->toContain('A preencher');
});

it('keeps a legal text already written in the panel', function () {
    ContentBlock::query()->where('key', 'terms_body')->update(['value' => '## Uso do site'.PHP_EOL.'Escrito pelo encarregado.']);
    runLegalModelMigration();

    expect(ContentBlock::query()->where('key', 'terms_body')->value('value'))->toContain('Escrito pelo encarregado.');
});

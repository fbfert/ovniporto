<?php

use App\Models\CommunityRule;
use App\Models\ContentBlock;
use App\Models\Faq;
use Database\Seeders\DatabaseSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(fn () => $this->seed(DatabaseSeeder::class));

it('seeds the 10 questions and the 5 community rules', function () {
    expect(Faq::query()->count())->toBe(10)
        ->and(CommunityRule::query()->count())->toBe(5);
});

it('never overwrites edited content when seeding again', function () {
    ContentBlock::query()->where('key', 'home_store')->update(['value' => 'Texto do painel']);

    $this->seed(DatabaseSeeder::class);

    expect(ContentBlock::query()->where('key', 'home_store')->value('value'))->toBe('Texto do painel');
});

// Titles live in resources/js/i18n/pt-BR.ts and reach the HTML through SSR (checked against the SSR server, not here).
it('serves every content page to anonymous visitors', function (string $path, string $component) {
    $this->get($path)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component($component));
})->with([
    ['/lenda', 'Content/Legend'],
    ['/faq', 'Content/Faq'],
    ['/comunidade', 'Content/Community'],
    ['/privacidade', 'Content/Legal'],
    ['/termos', 'Content/Legal'],
]);

it('keeps the legend "aguardando conteúdo" while its text is empty', function () {
    $this->get('/lenda')->assertInertia(fn (Assert $page) => $page->where('legendHtml', null));
});

it('renders the legend once it is written', function () {
    ContentBlock::query()->where('key', 'legend_body')->update(['value' => 'Numa noite de **geada**...']);

    $this->get('/lenda')->assertInertia(fn (Assert $page) => $page
        ->where('legendHtml', fn (string $html) => str_contains($html, '<strong>geada</strong>'))
    );
});

it('lists the questions in order with rendered answers', function () {
    $this->get('/faq')->assertInertia(fn (Assert $page) => $page
        ->has('faqs', 10)
        ->where('faqs.0.question', 'O OVNIPORTO já existe?')
        ->where('faqs.0.answerHtml', fn (string $html) => str_starts_with($html, '<p>'))
    );
});

it('shows the rules and the community channels', function () {
    ContentBlock::query()->where('key', 'link_instagram')->update(['value' => 'https://instagram.com/ovniporto']);

    $this->get('/comunidade')->assertInertia(fn (Assert $page) => $page
        ->has('rules', 5)
        ->where('rules.4.title', 'Humor sim, mentira não')
        ->where('community.instagram', 'https://instagram.com/ovniporto')
    );
});

it('marks privacy as a draft with the 9 topics, after "O que fazemos na prática"', function () {
    $this->get('/privacidade')->assertInertia(fn (Assert $page) => $page
        ->where('kind', 'privacy')
        ->where('draft', true)
        ->where('toc', fn ($toc) => collect($toc)->pluck('title')->all() === [
            'O que fazemos na prática', 'Dados coletados', 'Finalidades', 'Bases legais', 'Compartilhamento', 'Retenção',
            'Direitos do titular', 'Contato do encarregado', 'Cookies', 'Alterações',
        ])
        ->where('html', fn (string $html) => str_contains($html, 'PayPal') && str_contains($html, 'Melhor Envio') && str_contains($html, 'Google'))
    );
});

it('drops the draft notice once the text is marked final', function () {
    ContentBlock::query()->where('key', 'terms_final')->update(['value' => '1']);

    $this->get('/termos')->assertInertia(fn (Assert $page) => $page->where('kind', 'terms')->where('draft', false));
});

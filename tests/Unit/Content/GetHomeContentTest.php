<?php

use App\Application\Content\UseCases\GetHomeContent;
use App\Domain\Content\Contracts\ContentBlockRepository;

function contentRepository(array $stored): ContentBlockRepository
{
    return new class($stored) implements ContentBlockRepository
    {
        public function __construct(private array $stored) {}

        public function values(array $keys): array
        {
            return array_combine($keys, array_map(fn ($key) => $this->stored[$key] ?? null, $keys));
        }
    };
}

it('returns every home key, with empty strings for missing blocks', function () {
    $content = (new GetHomeContent(contentRepository(['home_intro' => 'Olá'])))->execute();

    expect($content)->toHaveKeys(GetHomeContent::KEYS)
        ->and($content['home_intro'])->toBe('Olá')
        ->and($content['home_place'])->toBe('');
});

it('keeps the legend null while it is not written, so the page shows "aguardando conteúdo"', function () {
    $content = (new GetHomeContent(contentRepository(['legend_body' => '   '])))->execute();

    expect($content['legend_body'])->toBeNull();
});

it('returns the legend once it exists', function () {
    $content = (new GetHomeContent(contentRepository(['legend_body' => 'Era uma vez um carro amarelo.'])))->execute();

    expect($content['legend_body'])->toBe('Era uma vez um carro amarelo.');
});

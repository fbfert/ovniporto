<?php

use App\Application\Content\UseCases\RenderContentBlocks;
use App\Application\Origin\UseCases\GetAtlas;
use App\Application\Origin\UseCases\GetAtlasCase;
use App\Application\Origin\UseCases\GetCachiDossier;
use App\Application\Origin\UseCases\GetOriginHub;
use App\Domain\Content\Contracts\ContentBlockRepository;
use App\Infrastructure\Content\CommonMarkRenderer;
use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\Repository;
use Tests\Support\FakeOriginLibrary;

function originHub(?string $relato): GetOriginHub
{
    $blocks = new class($relato) implements ContentBlockRepository
    {
        public function __construct(private ?string $relato) {}

        public function values(array $keys): array
        {
            return array_fill_keys($keys, $this->relato);
        }
    };

    return new GetOriginHub(new RenderContentBlocks($blocks, new CommonMarkRenderer, new Repository(new ArrayStore)), new FakeOriginLibrary);
}

it('keeps the yellow car relato empty until it is written, and counts what the doors lead to', function () {
    $hub = originHub(null)->execute();

    expect($hub['relatoHtml'])->toBeNull()
        ->and($hub['cachi'])->toMatchArray(['chapters' => 2])
        ->and($hub['cachi']['cover']['slug'])->toBe('cachi-aereo')
        ->and($hub['atlas'])->toBe(['cases' => 3, 'countries' => 3, 'sources' => 2]);
});

it('renders the relato once the founders write it', function () {
    expect(originHub('O Niva **subiu**.')->execute()['relatoHtml'])->toContain('<strong>subiu</strong>');
});

it('spells out the source kind of every Cachi statement and credits its photos', function () {
    $result = (new GetCachiDossier(new FakeOriginLibrary))->execute();
    [$night, $reports] = $result['dossier']['chapters'];

    expect($night['kinds'])->toBe([
        ['kind' => 'voice', 'label' => 'depoimento direto'],
        ['kind' => 'report', 'label' => 'relato de fenômeno'],
    ])
        ->and($reports['cases'][0]['kind'])->toBe(['kind' => 'ordinary', 'label' => 'hipótese ordinária'])
        ->and(array_keys($result['images']))->toBe(['cachi-aereo', 'recta-tin-tin']);
});

it('lists the Atlas cases as stamps with grade meanings and their image credits', function () {
    $atlas = (new GetAtlas(new FakeOriginLibrary))->execute();

    expect($atlas['cases'])->toHaveCount(3)
        ->and($atlas['cases'][0])->toMatchArray(['slug' => 'st-paul', 'category' => 'categoria st-paul', 'image' => 'atlas-st-paul'])
        ->and($atlas['cases'][0]['seal'])->toBe([
            ['grade' => 'A', 'meaning' => 'fonte primária ou ato oficial'],
            ['grade' => 'B', 'meaning' => 'fonte institucional ou documentação secundária muito sólida'],
        ])
        ->and($atlas['grades'][1])->toBe(['grade' => 'F', 'meaning' => 'informação não confirmada'])
        ->and(array_keys($atlas['images']))->toBe(['atlas-st-paul', 'atlas-lages']);
});

it('resolves a case with its catalogue sources and neighbours', function () {
    $result = (new GetAtlasCase(new FakeOriginLibrary))->execute('cachi');

    expect($result['case']['name'])->toBe('Cachi')
        ->and(array_column($result['case']['sources'], 'id'))->toBe(['la-nacion'])
        ->and($result['previous'])->toBe(['slug' => 'st-paul', 'name' => 'St-paul'])
        ->and($result['next'])->toBe(['slug' => 'lages', 'name' => 'Lages'])
        ->and($result['total'])->toBe(3);
});

it('has no neighbour past the ends, and returns null for an unknown case', function () {
    $useCase = new GetAtlasCase(new FakeOriginLibrary);

    expect($useCase->execute('st-paul')['previous'])->toBeNull()
        ->and($useCase->execute('lages')['next'])->toBeNull()
        ->and($useCase->execute('lages')['image']['kind'])->toBe('location-map')
        ->and($useCase->execute('nao-existe'))->toBeNull();
});

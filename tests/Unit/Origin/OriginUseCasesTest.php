<?php

use App\Application\Content\UseCases\RenderContentBlocks;
use App\Application\Origin\UseCases\GetAtlas;
use App\Application\Origin\UseCases\GetAtlasCase;
use App\Application\Origin\UseCases\GetCachiCover;
use App\Application\Origin\UseCases\GetCachiDossier;
use App\Application\Origin\UseCases\GetOriginHub;
use App\Application\Origin\UseCases\GetRelato;
use App\Domain\Content\Contracts\ContentBlockRepository;
use App\Infrastructure\Content\CommonMarkRenderer;
use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\Repository;
use Tests\Support\FakeOriginLibrary;

function relato(?string $markdown): GetRelato
{
    $blocks = new class($markdown) implements ContentBlockRepository
    {
        public function __construct(private ?string $markdown) {}

        public function values(array $keys): array
        {
            return array_fill_keys($keys, $this->markdown);
        }
    };

    return new GetRelato(new RenderContentBlocks($blocks, new CommonMarkRenderer, new Repository(new ArrayStore)));
}

function originHub(?string $relato): GetOriginHub
{
    return new GetOriginHub(relato($relato), new GetCachiCover(new FakeOriginLibrary), new FakeOriginLibrary);
}

it('keeps the yellow car relato empty until it is written, and counts what the doors lead to', function () {
    $hub = originHub(null)->execute();

    expect($hub['relatoOpening'])->toBeNull()
        ->and($hub['cachi'])->toMatchArray(['chapters' => 2])
        ->and($hub['cachi']['cover']['slug'])->toBe('cachi-aereo')
        ->and($hub['atlas'])->toBe(['cases' => 3, 'countries' => 3, 'sources' => 2]);
});

it('opens the hub with the first paragraphs of the relato once it is written', function () {
    expect(originHub("O Niva **subiu**.\n\nUm.\n\nDois.\n\nTrês.")->execute()['relatoOpening'])->toBe(['O Niva subiu.', 'Um.', 'Dois.']);
});

it('marks the short paragraphs of the relato as beats and leaves the long ones alone', function () {
    $html = relato("Era tarde da noite, e as pedras brancas da estrada pareciam engolir meu Niva amarelo.\n\nNada.")->execute()['html'];

    expect($html)->toContain('<p class="relato-beat">Nada.</p>')
        ->and($html)->toContain('<p>Era tarde da noite');
});

it('returns nothing while the relato is not written', function () {
    expect(relato('  ')->execute())->toBe(['html' => null, 'opening' => null]);
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

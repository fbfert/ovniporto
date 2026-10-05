<?php

namespace App\Application\Origin\UseCases;

use App\Application\Origin\OriginPresenter;
use App\Domain\Origin\Contracts\OriginLibrary;

/** /origem/atlas/{caso}: one ovnipuerto with its sources from the catalogue and the way to the next one. */
final readonly class GetAtlasCase
{
    public function __construct(private OriginLibrary $library) {}

    /** @return array<string, mixed>|null null when the slug is not an Atlas case */
    public function execute(string $slug): ?array
    {
        $atlas = $this->library->atlas();
        $cases = $atlas['cases'];
        $index = array_search($slug, array_column($cases, 'slug'), true);
        if ($index === false) {
            return null;
        }

        $case = $cases[$index];
        $catalogue = array_column($atlas['sources'], null, 'id');
        $image = $case['image']['file'] ?? null;
        $neighbour = fn (int $i) => isset($cases[$i]) ? ['slug' => $cases[$i]['slug'], 'name' => $cases[$i]['name']] : null;

        return [
            'case' => [
                ...$case,
                'category' => OriginPresenter::fact($case['facts'], 'Categoria'),
                'seal' => OriginPresenter::seal($case['seal']),
                'sources' => array_values(array_filter(array_map(fn (string $id) => $catalogue[$id] ?? null, $case['sources']))),
                'claims' => array_map(fn (array $claim) => [...$claim, 'seal' => OriginPresenter::seal($claim['grade'])], $case['claims'] ?? []),
            ],
            'image' => $image === null ? null : (OriginPresenter::credits($this->library->credits(), [$image])[$image] ?? null),
            'previous' => $neighbour($index - 1),
            'next' => $neighbour($index + 1),
            'total' => count($cases),
        ];
    }
}

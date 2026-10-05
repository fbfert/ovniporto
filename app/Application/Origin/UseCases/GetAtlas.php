<?php

namespace App\Application\Origin\UseCases;

use App\Application\Origin\OriginPresenter;
use App\Domain\Origin\Contracts\OriginLibrary;

/** /origem/atlas: the twelve ovnipuertos as stamps and map points, plus method, chronology, candidates and sources. */
final readonly class GetAtlas
{
    public function __construct(private OriginLibrary $library) {}

    /** @return array<string, mixed> */
    public function execute(): array
    {
        $atlas = $this->library->atlas();
        $credits = $this->library->credits();

        $cases = array_map(fn (array $case) => [
            'slug' => $case['slug'],
            'number' => $case['number'],
            'name' => $case['name'],
            'country' => $case['country'],
            'category' => OriginPresenter::fact($case['facts'], 'Categoria'),
            'seal' => OriginPresenter::seal($case['seal']),
            'coordinates' => $case['coordinates'] ?? null,
            'image' => $case['image']['file'] ?? null,
        ], $atlas['cases']);

        return [
            'title' => $atlas['title'],
            'subtitle' => $atlas['subtitle'] ?? '',
            'summary' => $atlas['summary'] ?? '',
            'revision' => $atlas['revision'] ?? null,
            'scope' => $atlas['scope'] ?? [],
            'method' => $atlas['method'] ?? [],
            'grades' => array_map(fn (array $g) => OriginPresenter::seal($g['grade'])[0], $atlas['grades']),
            'cases' => $cases,
            'timeline' => $atlas['comparativeTimeline'],
            'timelineNote' => $atlas['timelineNote'] ?? '',
            'crossQuestions' => $atlas['crossQuestions'] ?? [],
            'candidatesNote' => $atlas['candidatesNote'] ?? '',
            'candidates' => $atlas['candidates'],
            'sources' => $atlas['sources'],
            'limitations' => $atlas['limitations'] ?? [],
            'images' => OriginPresenter::credits($credits, array_values(array_filter(array_column($cases, 'image')))),
        ];
    }
}

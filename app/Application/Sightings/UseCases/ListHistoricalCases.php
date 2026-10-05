<?php

namespace App\Application\Sightings\UseCases;

use App\Domain\Sightings\Contracts\HistoricalCaseLibrary;

/** The "Casos históricos" section of /mapa: the cases grouped by region, and their map points. */
final readonly class ListHistoricalCases
{
    public function __construct(private HistoricalCaseLibrary $library) {}

    /**
     * @return array{eyebrow: string, title: string, intro: string, note: string, groups: list<array{region: string, label: string, cases: list<array<string, mixed>>}>, pins: list<array{slug: string, title: string, date: string, lat: float, lng: float}>}
     */
    public function execute(): array
    {
        $data = $this->library->collection();
        $groups = [];
        foreach ($data['regions'] as $region => $label) {
            $cases = array_values(array_filter($data['cases'], fn (array $case) => $case['region'] === $region));
            if ($cases !== []) {
                $groups[] = ['region' => $region, 'label' => $label, 'cases' => array_map($this->card(...), $cases)];
            }
        }

        return [
            'eyebrow' => (string) ($data['eyebrow'] ?? ''),
            'title' => (string) $data['title'],
            'intro' => (string) ($data['intro'] ?? ''),
            'note' => (string) ($data['note'] ?? ''),
            'groups' => $groups,
            'pins' => array_map(fn (array $case) => [
                'slug' => $case['slug'],
                'title' => $case['title'],
                'date' => $case['date'],
                'lat' => (float) $case['coordinates']['lat'],
                'lng' => (float) $case['coordinates']['lng'],
            ], $data['cases']),
        ];
    }

    /**
     * @param  array<string, mixed>  $case
     * @return array<string, mixed>
     */
    private function card(array $case): array
    {
        return array_intersect_key($case, array_flip(['slug', 'title', 'date', 'place', 'summary', 'image']));
    }
}

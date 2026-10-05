<?php

namespace App\Application\Sightings\UseCases;

use App\Domain\Sightings\Contracts\HistoricalCaseLibrary;

/** /mapa/casos/{slug}: one historical case, its region and its neighbours in reading order. */
final readonly class GetHistoricalCase
{
    public function __construct(private HistoricalCaseLibrary $library) {}

    /**
     * @return array{case: array<string, mixed>, region: string, previous: array{slug: string, title: string}|null, next: array{slug: string, title: string}|null, position: int, total: int, note: string}|null
     */
    public function execute(string $slug): ?array
    {
        $data = $this->library->collection();
        $cases = $data['cases'];
        $index = array_search($slug, array_column($cases, 'slug'), true);
        if ($index === false) {
            return null;
        }
        $neighbour = fn (int $i) => isset($cases[$i]) ? ['slug' => $cases[$i]['slug'], 'title' => $cases[$i]['title']] : null;

        return [
            'case' => $cases[$index],
            'region' => (string) $data['regions'][$cases[$index]['region']],
            'previous' => $neighbour($index - 1),
            'next' => $neighbour($index + 1),
            'position' => $index + 1,
            'total' => count($cases),
            'note' => (string) ($data['note'] ?? ''),
        ];
    }
}

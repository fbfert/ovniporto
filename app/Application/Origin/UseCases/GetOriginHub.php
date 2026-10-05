<?php

namespace App\Application\Origin\UseCases;

use App\Domain\Origin\Contracts\OriginLibrary;

/** /origem: the opening of the yellow car's relato (may still be empty) and the doors to Cachi and the Atlas. */
final readonly class GetOriginHub
{
    public function __construct(
        private GetRelato $relato,
        private GetCachiCover $cachiCover,
        private OriginLibrary $library,
    ) {}

    /**
     * @return array{relatoOpening: list<string>|null, cachi: array{chapters: int, cover: array<string, mixed>|null}, atlas: array{cases: int, countries: int, sources: int}}
     */
    public function execute(): array
    {
        $atlas = $this->library->atlas();

        return [
            'relatoOpening' => $this->relato->execute()['opening'],
            'cachi' => [
                'chapters' => count($this->library->cachi()['chapters']),
                'cover' => $this->cachiCover->execute(),
            ],
            'atlas' => [
                'cases' => count($atlas['cases']),
                'countries' => count(array_unique(array_column($atlas['cases'], 'country'))),
                'sources' => count($atlas['sources']),
            ],
        ];
    }
}

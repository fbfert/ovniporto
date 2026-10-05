<?php

namespace App\Application\Origin\UseCases;

use App\Application\Content\UseCases\RenderContentBlocks;
use App\Application\Origin\OriginPresenter;
use App\Domain\Origin\Contracts\OriginLibrary;

/** /origem: the yellow car's relato (editable, may still be empty) and the doors to Cachi and the Atlas. */
final readonly class GetOriginHub
{
    /** Editable block with the relato of the yellow car (key kept from the former /lenda page). */
    public const RELATO_BLOCK = 'legend_body';

    public function __construct(
        private RenderContentBlocks $blocks,
        private OriginLibrary $library,
    ) {}

    /**
     * @return array{relatoHtml: string|null, cachi: array{chapters: int, cover: array<string, mixed>|null}, atlas: array{cases: int, countries: int, sources: int}}
     */
    public function execute(): array
    {
        $cachi = $this->library->cachi();
        $atlas = $this->library->atlas();
        $cover = (string) ($cachi['cover'] ?? '');

        return [
            'relatoHtml' => $this->blocks->execute([self::RELATO_BLOCK])[self::RELATO_BLOCK],
            'cachi' => [
                'chapters' => count($cachi['chapters']),
                'cover' => OriginPresenter::credits($this->library->credits(), [$cover])[$cover] ?? null,
            ],
            'atlas' => [
                'cases' => count($atlas['cases']),
                'countries' => count(array_unique(array_column($atlas['cases'], 'country'))),
                'sources' => count($atlas['sources']),
            ],
        ];
    }
}

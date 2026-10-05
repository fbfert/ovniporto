<?php

namespace App\Application\Origin\UseCases;

use App\Application\Origin\OriginPresenter;
use App\Domain\Origin\Contracts\OriginLibrary;

/** /origem/cachi: the documented history, with every source kind spelled out and the credits of its photos. */
final readonly class GetCachiDossier
{
    public function __construct(private OriginLibrary $library) {}

    /** @return array{dossier: array<string, mixed>, images: array<string, array<string, mixed>>} */
    public function execute(): array
    {
        $dossier = $this->library->cachi();
        $slugs = [(string) ($dossier['cover'] ?? '')];

        foreach ($dossier['chapters'] as &$chapter) {
            $chapter['kinds'] = array_map(OriginPresenter::kind(...), $chapter['kinds'] ?? []);
            foreach (['cases', 'sources'] as $list) {
                foreach ($chapter[$list] ?? [] as $i => $item) {
                    $chapter[$list][$i]['kind'] = OriginPresenter::kind($item['kind']);
                    $slugs[] = (string) ($item['image'] ?? '');
                }
            }
            $slugs[] = (string) ($chapter['image'] ?? '');
            array_push($slugs, ...($chapter['images'] ?? []), ...array_column($chapter['gallery'] ?? [], 'image'));
        }
        unset($chapter);

        return ['dossier' => $dossier, 'images' => OriginPresenter::credits($this->library->credits(), $slugs)];
    }
}

<?php

namespace App\Application\Origin\UseCases;

use App\Application\Origin\OriginPresenter;
use App\Domain\Origin\Contracts\OriginLibrary;

/** The licensed aerial photo of the Cachi dossier, with its credit, for the pages that open the door to it. */
final readonly class GetCachiCover
{
    public function __construct(private OriginLibrary $library) {}

    /** @return array<string, mixed>|null */
    public function execute(): ?array
    {
        $cover = (string) ($this->library->cachi()['cover'] ?? '');

        return OriginPresenter::credits($this->library->credits(), [$cover])[$cover] ?? null;
    }
}

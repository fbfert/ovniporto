<?php

namespace App\Application\Place\UseCases;

use App\Domain\Content\Contracts\ContentBlockRepository;
use App\Domain\Place\Contracts\PlaceSpaceRepository;
use App\Domain\Place\Contracts\SitePhotoRepository;

final readonly class GetPlacePage
{
    public function __construct(
        private PlaceSpaceRepository $spaces,
        private SitePhotoRepository $photos,
        private ContentBlockRepository $blocks,
    ) {}

    /** @return array{spaces: list<array<string, mixed>>, photos: list<array{url: string, alt: string, caption: ?string, takenAt: ?string}>, map3d: ?string} */
    public function execute(): array
    {
        return [
            'spaces' => array_map(fn ($space) => $space->toArray(), $this->spaces->all()),
            'photos' => $this->photos->all(),
            // Already checked against the allowed hosts when the admin saved it (EmbedPolicy).
            'map3d' => $this->blocks->values([ManagePlace::MAP3D_KEY])[ManagePlace::MAP3D_KEY] ?: null,
        ];
    }
}

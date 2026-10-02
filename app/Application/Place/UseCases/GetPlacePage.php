<?php

namespace App\Application\Place\UseCases;

use App\Domain\Place\Contracts\PlaceSpaceRepository;
use App\Domain\Place\Contracts\SitePhotoRepository;

final readonly class GetPlacePage
{
    public function __construct(
        private PlaceSpaceRepository $spaces,
        private SitePhotoRepository $photos,
    ) {}

    /** @return array{spaces: list<array<string, mixed>>, photos: list<array{url: string, alt: string, caption: ?string, takenAt: ?string}>} */
    public function execute(): array
    {
        return [
            'spaces' => array_map(fn ($space) => $space->toArray(), $this->spaces->all()),
            'photos' => $this->photos->all(),
        ];
    }
}

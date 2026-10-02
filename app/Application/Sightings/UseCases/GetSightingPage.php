<?php

namespace App\Application\Sightings\UseCases;

use App\Domain\Sightings\Contracts\SightingReadRepository;
use App\Domain\Sightings\Data\SightingCard;

final readonly class GetSightingPage
{
    public const NEARBY_KM = 20;

    public const NEARBY_LIMIT = 3;

    public function __construct(private SightingReadRepository $sightings) {}

    /**
     * The public report, or — for its author only — the report still in analysis.
     *
     * @return array{sighting: array<string, mixed>, nearby: list<array<string, mixed>>, ownPending: bool}|null
     */
    public function execute(int $id, ?int $viewerId): ?array
    {
        $sighting = $this->sightings->findPublic($id);
        $ownPending = false;
        if ($sighting === null && $viewerId !== null) {
            $sighting = $this->sightings->findForAuthor($id, $viewerId);
            $ownPending = $sighting !== null;
        }
        if ($sighting === null) {
            return null;
        }

        $nearby = $ownPending ? [] : $this->sightings->nearby(
            (float) $sighting['lat'],
            (float) $sighting['lng'],
            self::NEARBY_KM,
            $id,
            self::NEARBY_LIMIT,
        );

        return [
            'sighting' => $sighting,
            'nearby' => array_map(fn (SightingCard $card) => $card->toArray(), $nearby),
            'ownPending' => $ownPending,
        ];
    }
}

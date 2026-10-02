<?php

namespace App\Domain\Sightings\Contracts;

use App\Domain\Sightings\Data\SightingCard;
use App\Domain\Sightings\Data\SightingFilters;

/**
 * Public read side of the Livro de avistamentos. Implementations MUST only
 * expose approved and published sightings, except findForAuthor().
 */
interface SightingReadRepository
{
    public function countApproved(): int;

    /** @return list<SightingCard> */
    public function latestApproved(int $limit): array;

    public function countPublic(SightingFilters $filters): int;

    /**
     * Every public report matching the filters, as map pins: only the 7 public fields.
     *
     * @return list<array{id: int, type: string, lat: float, lng: float, date: string, nickname: string, thumb: ?string}>
     */
    public function pins(SightingFilters $filters): array;

    /**
     * Newest first, one page of polaroids.
     *
     * @return list<SightingCard>
     */
    public function page(SightingFilters $filters, int $page, int $perPage): array;

    /** @return array<string, mixed>|null the public detail, or null when not approved and published */
    public function findPublic(int $id): ?array;

    /** @return array<string, mixed>|null the author's own report in any status (photos through signed URLs) */
    public function findForAuthor(int $id, int $memberId): ?array;

    /**
     * Public reports within the radius, nearest first, excluding one id.
     *
     * @return list<SightingCard>
     */
    public function nearby(float $lat, float $lng, int $radiusKm, int $excludeId, int $limit): array;
}

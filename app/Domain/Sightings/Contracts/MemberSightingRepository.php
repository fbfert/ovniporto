<?php

namespace App\Domain\Sightings\Contracts;

/** A member's own reports, whatever their status. Never used for public pages. */
interface MemberSightingRepository
{
    /**
     * Newest first.
     *
     * @return list<array{id: int, type: string, status: string, moderationNote: ?string, observedDate: string, placeLabel: ?string, createdAt: string}>
     */
    public function ownedBy(int $memberId): array;

    /** Deletes the report and its photo files when it belongs to the member; false otherwise. */
    public function deleteOwned(int $memberId, int $sightingId): bool;

    /** Deletes every report of the member, with their photo files. */
    public function deleteAllOf(int $memberId): void;
}

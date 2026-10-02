<?php

namespace App\Domain\Sightings\Contracts;

use App\Domain\Sightings\Data\ModerationDecision;
use App\Domain\Sightings\SightingStatus;
use DateTimeImmutable;

/** The tower's view of the reports: every status, the author's real data, photos by signed URL. */
interface ModerationRepository
{
    /** @return array<string, int> report count per status value */
    public function countByStatus(): array;

    /**
     * Pending reports oldest first (whoever waited longest goes first); the other tabs newest decision first.
     *
     * @return list<array{id: int, type: string, nickname: string, observedDate: string, city: ?string, submittedAt: string, thumb: ?string, photoCount: int}>
     */
    public function queue(SightingStatus $status, int $page, int $perPage): array;

    /** @return array<string, mixed>|null everything the review screen shows, author included */
    public function review(int $id): ?array;

    /** @return array{previous: ?int, next: ?int} neighbours in the same tab, in queue order */
    public function neighbours(int $id): array;

    public function statusOf(int $id): ?SightingStatus;

    /** @return array{status: string, moderationNote: ?string, publishedAt: ?string}|null */
    public function snapshot(int $id): ?array;

    public function apply(int $id, ModerationDecision $decision, int $moderatorId, DateTimeImmutable $now): void;

    /** Oldest pending report's submission time, for "Precisa de você". */
    public function oldestPendingSince(): ?DateTimeImmutable;
}

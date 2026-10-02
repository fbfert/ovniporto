<?php

namespace App\Application\Sightings\UseCases;

use App\Domain\Audit\Contracts\Auditor;
use App\Domain\Sightings\Contracts\ModerationRepository;
use App\Domain\Sightings\SightingStatus;
use DateTimeImmutable;

/** What the moderation queue and the review screen show. */
final readonly class ReviewSightings
{
    public const PER_PAGE = 20;

    public function __construct(
        private ModerationRepository $sightings,
        private Auditor $auditor,
    ) {}

    /** @return array{items: list<array<string, mixed>>, counts: array<string, int>, page: int, hasMore: bool} */
    public function queue(SightingStatus $status, int $page, DateTimeImmutable $now): array
    {
        $page = max(1, $page);
        $counts = $this->sightings->countByStatus();
        $items = array_map(
            fn (array $item) => [...$item, 'waitingHours' => self::hoursSince($item['submittedAt'], $now)],
            $this->sightings->queue($status, $page, self::PER_PAGE),
        );

        return [
            'items' => $items,
            'counts' => $counts,
            'page' => $page,
            'hasMore' => $page * self::PER_PAGE < ($counts[$status->value] ?? 0),
        ];
    }

    /** @return array{sighting: array<string, mixed>, history: list<array<string, mixed>>, neighbours: array{previous: ?int, next: ?int}}|null */
    public function review(int $id, DateTimeImmutable $now): ?array
    {
        $sighting = $this->sightings->review($id);
        if ($sighting === null) {
            return null;
        }

        return [
            'sighting' => [...$sighting, 'waitingHours' => self::hoursSince((string) $sighting['submittedAt'], $now)],
            'history' => $this->auditor->history('sighting', $id),
            'neighbours' => $this->sightings->neighbours($id),
        ];
    }

    /** The report after this one in its tab, where the moderator goes after deciding. */
    public function nextAfter(int $id): ?int
    {
        return $this->sightings->neighbours($id)['next'];
    }

    /** Whole hours since an ISO date: what "tempo desde o envio" shows. */
    public static function hoursSince(string $iso, DateTimeImmutable $now): int
    {
        return max(0, intdiv($now->getTimestamp() - (new DateTimeImmutable($iso))->getTimestamp(), 3600));
    }
}

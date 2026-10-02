<?php

namespace App\Application\Panel\UseCases;

use App\Domain\Panel\PanelArea;
use App\Domain\Sightings\Contracts\ModerationRepository;
use App\Domain\Sightings\SightingStatus;
use DateTimeImmutable;

/**
 * The panel's first screen, by area the operator can open. The full
 * dashboard (goals, 12-week chart, orders) arrives with the store.
 */
final readonly class GetPanelHome
{
    /** A report waiting longer than this shows up in "Precisa de você". */
    public const PENDING_ALERT_HOURS = 48;

    public function __construct(private ModerationRepository $sightings) {}

    /**
     * @param  list<PanelArea>  $areas
     * @return array{sightings: array{pending: int, changesRequested: int, approved: int, oldestPendingHours: ?int, overdue: bool}|null}
     */
    public function execute(array $areas, DateTimeImmutable $now): array
    {
        if (! in_array(PanelArea::Sightings, $areas, true)) {
            return ['sightings' => null];
        }

        $counts = $this->sightings->countByStatus();
        $oldest = $this->sightings->oldestPendingSince();

        return ['sightings' => [
            'pending' => $counts[SightingStatus::Pending->value] ?? 0,
            'changesRequested' => $counts[SightingStatus::ChangesRequested->value] ?? 0,
            'approved' => $counts[SightingStatus::Approved->value] ?? 0,
            'oldestPendingHours' => $oldest === null ? null : intdiv(max(0, $now->getTimestamp() - $oldest->getTimestamp()), 3600),
            'overdue' => $oldest !== null && $now->getTimestamp() - $oldest->getTimestamp() > self::PENDING_ALERT_HOURS * 3600,
        ]];
    }
}

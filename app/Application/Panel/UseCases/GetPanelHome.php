<?php

namespace App\Application\Panel\UseCases;

use App\Domain\Content\Contracts\ContentBlockRepository;
use App\Domain\Panel\Contracts\DashboardRepository;
use App\Domain\Panel\PanelArea;
use App\Domain\Sightings\Contracts\ModerationRepository;
use App\Domain\Sightings\SightingStatus;
use DateTimeImmutable;

/**
 * The panel's first screen: counts, the 6-month goals from the launch date,
 * twelve weeks of reports and sales, and "Precisa de você" — only for the
 * areas the operator can open.
 */
final readonly class GetPanelHome
{
    /** A report waiting longer than this shows up in "Precisa de você". */
    public const PENDING_ALERT_HOURS = 48;

    public const PRODUCTION_ALERT_DAYS = 2;

    public const TRACKING_ALERT_DAYS = 7;

    public const GOAL_MONTHS = 6;

    public const WEEKS = 12;

    public function __construct(
        private ModerationRepository $sightings,
        private DashboardRepository $dashboard,
        private ContentBlockRepository $blocks,
    ) {}

    /**
     * @param  list<PanelArea>  $areas
     * @return array<string, mixed>
     */
    public function execute(array $areas, DateTimeImmutable $now): array
    {
        $seesSightings = in_array(PanelArea::Sightings, $areas, true);
        $seesOrders = in_array(PanelArea::Orders, $areas, true);
        $counts = $this->dashboard->counts($now);

        return [
            'counts' => [
                'members' => $counts['members'],
                'waitlist' => $counts['waitlist'],
                'sightings' => $seesSightings ? $counts['sightings'] : null,
                'orders' => $seesOrders ? $counts['orders'] : null,
                'revenueMonthCents' => $seesOrders ? $counts['revenueMonthCents'] : null,
            ],
            'goals' => $this->goals($counts, $now),
            'weekly' => $this->dashboard->weekly($now, self::WEEKS),
            'needsYou' => $this->needsYou($seesSightings, $seesOrders, $now),
            'sightings' => $seesSightings ? $this->sightingsSummary($now) : null,
        ];
    }

    /**
     * @param  array{members: int, sightings: int, orders: int, revenueMonthCents: int, waitlist: int}  $counts
     * @return array{launch: string, deadline: string, items: list<array{key: string, current: int, target: int}>}|null
     */
    private function goals(array $counts, DateTimeImmutable $now): ?array
    {
        $values = $this->blocks->values(['launch_date', 'goal_members', 'goal_sightings', 'goal_orders']);
        if (blank($values['launch_date'])) {
            return null;
        }
        $launch = new DateTimeImmutable((string) $values['launch_date']);
        $items = [];
        foreach (['members' => 'goal_members', 'sightings' => 'goal_sightings', 'orders' => 'goal_orders'] as $key => $goal) {
            if (filled($values[$goal]) && (int) $values[$goal] > 0) {
                $items[] = ['key' => $key, 'current' => $counts[$key], 'target' => (int) $values[$goal]];
            }
        }

        return [
            'launch' => $launch->format('Y-m-d'),
            'deadline' => $launch->modify('+'.self::GOAL_MONTHS.' months')->format('Y-m-d'),
            'items' => $items,
        ];
    }

    /** @return list<array{kind: string, ref: string, since: string}> */
    private function needsYou(bool $seesSightings, bool $seesOrders, DateTimeImmutable $now): array
    {
        $items = [];
        if ($seesSightings) {
            foreach ($this->dashboard->sightingsWaitingSince($now->modify('-'.self::PENDING_ALERT_HOURS.' hours')) as $s) {
                $items[] = ['kind' => 'sighting', 'ref' => (string) $s['id'], 'since' => $s['since']];
            }
        }
        if ($seesOrders) {
            foreach ($this->dashboard->ordersAwaitingProduction($now->modify('-'.self::PRODUCTION_ALERT_DAYS.' days')) as $o) {
                $items[] = ['kind' => 'production', 'ref' => $o['number'], 'since' => $o['since']];
            }
            foreach ($this->dashboard->ordersWithoutTracking($now->modify('-'.self::TRACKING_ALERT_DAYS.' days')) as $o) {
                $items[] = ['kind' => 'tracking', 'ref' => $o['number'], 'since' => $o['since']];
            }
        }

        return $items;
    }

    /** @return array{pending: int, changesRequested: int, approved: int, oldestPendingHours: ?int, overdue: bool} */
    private function sightingsSummary(DateTimeImmutable $now): array
    {
        $counts = $this->sightings->countByStatus();
        $oldest = $this->sightings->oldestPendingSince();

        return [
            'pending' => $counts[SightingStatus::Pending->value] ?? 0,
            'changesRequested' => $counts[SightingStatus::ChangesRequested->value] ?? 0,
            'approved' => $counts[SightingStatus::Approved->value] ?? 0,
            'oldestPendingHours' => $oldest === null ? null : intdiv(max(0, $now->getTimestamp() - $oldest->getTimestamp()), 3600),
            'overdue' => $oldest !== null && $now->getTimestamp() - $oldest->getTimestamp() > self::PENDING_ALERT_HOURS * 3600,
        ];
    }
}

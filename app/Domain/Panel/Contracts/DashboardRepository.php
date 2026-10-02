<?php

namespace App\Domain\Panel\Contracts;

use DateTimeImmutable;

/** The numbers on the panel's first screen. */
interface DashboardRepository
{
    /** @return array{members: int, sightings: int, orders: int, revenueMonthCents: int, waitlist: int} */
    public function counts(DateTimeImmutable $now): array;

    /**
     * Monday-based weeks, oldest first.
     *
     * @return list<array{week: string, sightings: int, orders: int}>
     */
    public function weekly(DateTimeImmutable $now, int $weeks): array;

    /** @return list<array{id: int, since: string}> pending reports submitted before the cutoff */
    public function sightingsWaitingSince(DateTimeImmutable $cutoff): array;

    /** @return list<array{number: string, since: string}> paid orders not yet in production, paid before the cutoff */
    public function ordersAwaitingProduction(DateTimeImmutable $cutoff): array;

    /** @return list<array{number: string, since: string}> paid/in-production orders without tracking, paid before the cutoff */
    public function ordersWithoutTracking(DateTimeImmutable $cutoff): array;
}

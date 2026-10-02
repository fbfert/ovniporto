<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Orders\OrderStatus;
use App\Domain\Panel\Contracts\DashboardRepository;
use App\Domain\Sightings\SightingStatus;
use App\Models\Member;
use App\Models\NewsletterSubscriber;
use App\Models\Order;
use App\Models\Sighting;
use DateTimeImmutable;
use Illuminate\Database\Eloquent\Builder;

final class EloquentDashboardRepository implements DashboardRepository
{
    /** Orders that count as sold: paid and everything after, except refunds. */
    private const SOLD = ['paid', 'in_production', 'shipped', 'delivered'];

    public function counts(DateTimeImmutable $now): array
    {
        $monthStart = $now->modify('first day of this month')->setTime(0, 0);

        return [
            'members' => Member::query()->whereNotNull('terms_accepted_at')->count(),
            'sightings' => Sighting::query()->where('status', SightingStatus::Approved)->count(),
            'orders' => Order::query()->whereIn('status', self::SOLD)->count(),
            'revenueMonthCents' => (int) Order::query()->whereIn('status', self::SOLD)->where('paid_at', '>=', $monthStart)->sum('total_cents'),
            'waitlist' => NewsletterSubscriber::query()->whereNotNull('confirmed_at')->count(),
        ];
    }

    public function weekly(DateTimeImmutable $now, int $weeks): array
    {
        $start = $now->modify('monday this week')->setTime(0, 0)->modify('-'.($weeks - 1).' weeks');
        $sightings = Sighting::query()->where('created_at', '>=', $start)->pluck('created_at');
        $orders = Order::query()->whereIn('status', self::SOLD)->where('paid_at', '>=', $start)->pluck('paid_at');

        $series = [];
        for ($i = 0; $i < $weeks; $i++) {
            $from = $start->modify("+{$i} weeks");
            $to = $from->modify('+1 week');
            $inWeek = fn ($at) => $at !== null && $at >= $from && $at < $to;
            $series[] = [
                'week' => $from->format('Y-m-d'),
                'sightings' => $sightings->filter($inWeek)->count(),
                'orders' => $orders->filter($inWeek)->count(),
            ];
        }

        return $series;
    }

    public function sightingsWaitingSince(DateTimeImmutable $cutoff): array
    {
        return Sighting::query()
            ->where('status', SightingStatus::Pending)
            ->whereRaw('coalesce(submitted_at, created_at) < ?', [$cutoff->format('Y-m-d H:i:s')])
            ->orderByRaw('coalesce(submitted_at, created_at)')
            ->limit(10)
            ->get()
            ->map(fn (Sighting $s) => ['id' => $s->id, 'since' => ($s->submitted_at ?? $s->created_at)->toIso8601String()])
            ->values()
            ->all();
    }

    public function ordersAwaitingProduction(DateTimeImmutable $cutoff): array
    {
        return $this->orders(Order::query()->where('status', OrderStatus::Paid)->where('paid_at', '<', $cutoff));
    }

    public function ordersWithoutTracking(DateTimeImmutable $cutoff): array
    {
        return $this->orders(Order::query()
            ->whereIn('status', [OrderStatus::Paid, OrderStatus::InProduction])
            ->where('pickup', false)
            ->whereNull('tracking_code')
            ->where('paid_at', '<', $cutoff));
    }

    /**
     * @param  Builder<Order>  $query
     * @return list<array{number: string, since: string}>
     */
    private function orders($query): array
    {
        return $query->orderBy('paid_at')->limit(10)->get()
            ->map(fn (Order $o) => ['number' => $o->number, 'since' => (string) $o->paid_at?->toIso8601String()])
            ->values()
            ->all();
    }
}

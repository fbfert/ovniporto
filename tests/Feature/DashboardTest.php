<?php

use App\Domain\Orders\OrderStatus;
use App\Models\ContentBlock;
use App\Models\Member;
use App\Models\Order;
use App\Models\Sighting;
use Inertia\Testing\AssertableInertia as Assert;

function dashboardOrder(OrderStatus $status, $paidAt, array $overrides = []): Order
{
    static $n = 500;
    $n++;

    return Order::query()->create([
        'number' => sprintf('OVP-2026-%06d', $n),
        'status' => $status,
        'pickup' => false,
        'subtotal_cents' => 800,
        'shipping_cents' => 0,
        'total_cents' => 800,
        'paid_at' => $paidAt,
        ...$overrides,
    ]);
}

it('lists what needs attention, exactly at the 48 h, 2-day and 7-day limits', function () {
    $admin = Member::factory()->role('admin')->create();
    Sighting::factory()->create(['submitted_at' => now()->subHours(47)]);
    $late = Sighting::factory()->create(['submitted_at' => now()->subHours(49)]);
    dashboardOrder(OrderStatus::Paid, now()->subDay());
    $waiting = dashboardOrder(OrderStatus::Paid, now()->subDays(3));
    dashboardOrder(OrderStatus::InProduction, now()->subDays(6));
    $untracked = dashboardOrder(OrderStatus::InProduction, now()->subDays(8));
    dashboardOrder(OrderStatus::InProduction, now()->subDays(9), ['tracking_code' => 'AA1BR']);

    $this->actingAs($admin)->get('/painel')->assertInertia(fn (Assert $page) => $page
        ->component('Panel/Home')
        ->where('needsYou', [
            ['kind' => 'sighting', 'ref' => (string) $late->id, 'since' => $late->submitted_at->toIso8601String()],
            ['kind' => 'production', 'ref' => $waiting->number, 'since' => $waiting->paid_at->toIso8601String()],
            ['kind' => 'tracking', 'ref' => $untracked->number, 'since' => $untracked->paid_at->toIso8601String()],
        ])
    );
});

it('counts sales, the month revenue and twelve weeks of activity', function () {
    $admin = Member::factory()->role('admin')->create();
    dashboardOrder(OrderStatus::Paid, now());
    dashboardOrder(OrderStatus::Refunded, now());
    dashboardOrder(OrderStatus::PendingPayment, null);
    Sighting::factory()->approved()->create();

    $this->actingAs($admin)->get('/painel')->assertInertia(fn (Assert $page) => $page
        ->where('counts.orders', 1)
        ->where('counts.revenueMonthCents', 800)
        ->where('counts.sightings', 1)
        ->has('weekly', 12)
        ->where('weekly.11.orders', 1)
    );
});

it('measures the 6-month goals from the launch date', function () {
    $admin = Member::factory()->role('admin')->create();
    ContentBlock::query()->create(['key' => 'launch_date', 'value' => '2026-10-01']);
    ContentBlock::query()->create(['key' => 'goal_members', 'value' => '500']);

    $this->actingAs($admin)->get('/painel')->assertInertia(fn (Assert $page) => $page
        ->where('goals.deadline', '2027-04-01')
        ->where('goals.items.0.key', 'members')
        ->where('goals.items.0.target', 500)
        ->where('goals.items.0.current', 1)
    );
});

it('shows a moderator only the moderation side', function () {
    dashboardOrder(OrderStatus::Paid, now()->subDays(3));

    $this->actingAs(Member::factory()->role('moderator')->create())->get('/painel')->assertInertia(fn (Assert $page) => $page
        ->where('counts.orders', null)
        ->where('counts.revenueMonthCents', null)
        ->where('needsYou', [])
    );
});

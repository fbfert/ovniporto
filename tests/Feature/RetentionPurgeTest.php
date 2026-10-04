<?php

use App\Models\Member;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Tests\Support\Orders;

beforeEach(fn () => Mail::fake());

it('deletes an anonymized order and its tax data once the retention date passes', function () {
    $member = Member::factory()->create(['nickname' => 'coruja', 'email' => 'coruja@serra.test']);
    $order = Orders::paidBy($member, 'coruja@serra.test', Carbon::parse('2026-09-20 15:00:00'));
    $active = Orders::paidBy(Member::factory()->create(), 'outra@serra.test', Carbon::parse('2026-09-20 15:00:00'));
    $this->actingAs($member)->delete('/conta', ['confirmation' => 'coruja']);

    $this->travelTo(Carbon::parse('2031-09-20 14:59:00'));
    $this->artisan('privacy:purge-orders')->expectsOutputToContain('0 pedido(s)')->assertSuccessful();
    expect(Order::query()->find($order->id))->not->toBeNull();

    $this->travelTo(Carbon::parse('2031-09-20 15:00:00'));
    $this->artisan('privacy:purge-orders')->expectsOutputToContain('1 pedido(s)')->assertSuccessful();

    expect(Order::query()->find($order->id))->toBeNull()
        ->and(OrderItem::query()->where('order_id', $order->id)->exists())->toBeFalse()
        ->and(Order::query()->find($active->id))->not->toBeNull();
});

it('purges right away an unpaid order of a deleted account (nothing to keep for tax law)', function () {
    $member = Member::factory()->create(['nickname' => 'coruja', 'email' => 'coruja@serra.test']);
    $order = Orders::paidBy($member, 'coruja@serra.test');
    $this->actingAs($member)->delete('/conta', ['confirmation' => 'coruja']);

    $this->artisan('privacy:purge-orders')->assertSuccessful();

    expect(Order::query()->find($order->id))->toBeNull();
});

it('runs every day', function () {
    $events = collect(app(Schedule::class)->events())
        ->filter(fn ($event) => str_contains((string) $event->command, 'privacy:purge-orders'));

    expect($events)->toHaveCount(1)
        ->and($events->first()->expression)->toBe('30 3 * * *');
});

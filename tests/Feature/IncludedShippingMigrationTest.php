<?php

use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Tests\Support\Orders;

it('keeps the quoted price of existing orders equal to what the buyer paid', function () {
    $migration = require database_path('migrations/2026_10_16_000500_add_included_shipping.php');
    $migration->down();
    $order = Orders::paidBy(null, 'ana@example.com', now());
    $migration->up();

    expect(DB::table('orders')->where('id', $order->id)->value('shipping_cost_cents'))->toBe(1590)
        ->and(Order::query()->findOrFail($order->id)->items()->sole()->shipping_included)->toBeFalsy();
});

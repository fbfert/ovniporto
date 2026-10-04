<?php

namespace Tests\Support;

use App\Models\Member;
use App\Models\Order;
use DateTimeInterface;

/** Orders straight in the database, for privacy tests that only care about who they belong to. */
final class Orders
{
    private static int $sequence = 0;

    public static function paidBy(?Member $member, string $email, ?DateTimeInterface $paidAt = null): Order
    {
        $order = Order::query()->create([
            'number' => sprintf('OVP-2026-%06d', ++self::$sequence),
            'member_id' => $member?->id,
            'status' => $paidAt === null ? 'pending_payment' : 'paid',
            'customer_name' => $member?->name ?? 'Visitante',
            'customer_email' => $email,
            'customer_phone' => '49999990000',
            'customer_cpf' => '529.982.247-25',
            'address' => ['cep' => '88501-000', 'street' => 'Rua da Serra', 'number' => '10', 'complement' => null, 'district' => 'Centro', 'city' => 'Lages', 'state' => 'SC'],
            'subtotal_cents' => 800,
            'shipping_cents' => 1590,
            'total_cents' => 2390,
            'paid_at' => $paidAt,
        ]);
        $order->items()->create([
            'product_name' => 'Adesivo OVNIPORTO', 'variant_name' => 'Único', 'sku' => 'OVP-ADESIVO',
            'unit_price_cents' => 800, 'quantity' => 1, 'line_cents' => 800,
        ]);

        return $order;
    }
}

<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Orders\Contracts\OrderRepository;
use App\Domain\Orders\Cpf;
use App\Domain\Orders\Data\NewOrder;
use App\Domain\Orders\OrderNumber;
use App\Domain\Orders\OrderStatus;
use App\Models\Order;
use App\Models\OrderEvent;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use DateTimeInterface;
use Illuminate\Support\Facades\DB;

final class EloquentOrderRepository implements OrderRepository
{
    /** Shown in the panel where the customer's name was, after the account is deleted. */
    private const DELETED_HOLDER = 'Titular excluído';

    public function create(NewOrder $order): array
    {
        return DB::transaction(function () use ($order) {
            $number = $this->nextNumber((int) now()->format('Y'));
            $model = Order::query()->create([
                'number' => $number,
                'member_id' => $order->memberId,
                'cart_token' => $order->cartToken,
                'status' => OrderStatus::PendingPayment,
                'customer_name' => $order->name,
                'customer_email' => $order->email,
                'customer_phone' => $order->phone,
                'customer_cpf' => $order->cpf,
                'address' => $order->address,
                'pickup' => $order->isPickup(),
                'shipping_option_id' => $order->shipping?->id,
                'shipping_carrier' => $order->shipping?->carrier,
                'shipping_service' => $order->shipping?->service,
                'shipping_days' => $order->shipping?->days,
                'subtotal_cents' => $order->totals->subtotal->cents,
                'shipping_cents' => $order->totals->shipping->cents,
                'total_cents' => $order->totals->total->cents,
            ]);

            foreach ($order->items as $item) {
                $model->items()->create([
                    'product_variant_id' => $item['variantId'],
                    'product_name' => $item['productName'],
                    'variant_name' => $item['variantName'],
                    'sku' => (string) ProductVariant::query()->whereKey($item['variantId'])->value('sku'),
                    'unit_price_cents' => $item['unitPriceCents'],
                    'quantity' => $item['quantity'],
                    'line_cents' => $item['unitPriceCents'] * $item['quantity'],
                    'made_to_order' => $item['madeToOrder'],
                    'production_days' => $item['productionDays'],
                    'weight_grams' => $item['weightGrams'],
                ]);
            }
            $this->event($model->id, null, OrderStatus::PendingPayment, 'customer', $order->memberId, null);

            return ['id' => $model->id, 'number' => $number];
        });
    }

    public function findByNumber(string $number): ?array
    {
        $order = Order::query()->where('number', $number)->first();

        return $order === null ? null : $this->snapshot($order);
    }

    public function findByPaymentOrderId(string $paymentOrderId): ?array
    {
        $order = Order::query()->where('payment_order_id', $paymentOrderId)->first();

        return $order === null ? null : $this->snapshot($order);
    }

    public function setPaymentOrderId(int $orderId, string $paymentOrderId): void
    {
        Order::query()->whereKey($orderId)->update(['payment_order_id' => $paymentOrderId]);
    }

    public function locked(int $orderId, callable $work): mixed
    {
        return DB::transaction(fn () => $work($this->snapshot(Order::query()->lockForUpdate()->findOrFail($orderId))));
    }

    public function changeStatus(int $orderId, OrderStatus $from, OrderStatus $to, string $actor, ?int $actorId, ?string $note, array $changes = []): void
    {
        Order::query()->whereKey($orderId)->update(['status' => $to, ...$changes]);
        $this->event($orderId, $from, $to, $actor, $actorId, $note);
    }

    public function addNote(int $orderId, OrderStatus $status, string $actor, string $note): void
    {
        $this->event($orderId, $status, $status, $actor, null, $note);
    }

    public function decrementStock(int $orderId): void
    {
        $this->moveStock($orderId, -1, 'Venda');
    }

    public function restoreStock(int $orderId, ?int $actorId): void
    {
        $this->moveStock($orderId, 1, 'Reembolso antes do envio', $actorId);
    }

    /** In-stock items only; every change lands in the stock history with the order. */
    private function moveStock(int $orderId, int $sign, string $reason, ?int $actorId = null): void
    {
        $order = Order::query()->findOrFail($orderId);
        $items = OrderItem::query()->where('order_id', $orderId)->where('made_to_order', false)->whereNotNull('product_variant_id')->get();
        foreach ($items as $item) {
            $variant = ProductVariant::query()->lockForUpdate()->find($item->product_variant_id);
            if ($variant === null || $variant->stock_qty === null) {
                continue;
            }
            $before = $variant->stock_qty;
            $after = max(0, $before + $sign * $item->quantity);
            $variant->update(['stock_qty' => $after]);
            DB::table('stock_movements')->insert([
                'product_variant_id' => $variant->id,
                'delta' => $after - $before,
                'quantity_after' => $after,
                'reason' => "{$reason} · {$order->number}",
                'actor_id' => $actorId,
                'order_id' => $orderId,
                'created_at' => now(),
            ]);
        }
    }

    public function abandonedBefore(DateTimeInterface $cutoff): array
    {
        /** @var list<int> $ids */
        $ids = Order::query()
            ->where('status', OrderStatus::PendingPayment)
            ->where('created_at', '<', $cutoff)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        return $ids;
    }

    public function inTransit(): array
    {
        return Order::query()
            ->where('status', OrderStatus::Shipped)
            ->whereNotNull('shipment_id')
            ->get(['id', 'shipment_id'])
            ->map(fn (Order $o) => ['id' => $o->id, 'shipmentId' => (string) $o->shipment_id])
            ->values()
            ->all();
    }

    public function setShipment(int $orderId, string $shipmentId, ?string $trackingCode, ?string $trackingUrl): void
    {
        Order::query()->whereKey($orderId)->update([
            'shipment_id' => $shipmentId,
            'tracking_code' => $trackingCode,
            'tracking_url' => $trackingUrl,
        ]);
    }

    public function shipmentData(int $orderId): ?array
    {
        $order = Order::query()->with('items')->find($orderId);
        if ($order === null || $order->address === null) {
            return null;
        }

        return [
            'number' => $order->number,
            'status' => $order->status,
            'serviceId' => (string) $order->shipping_option_id,
            'name' => (string) $order->customer_name,
            'email' => (string) $order->customer_email,
            'phone' => (string) $order->customer_phone,
            'cpf' => (string) $order->customer_cpf,
            'address' => $order->address,
            'items' => $order->items->map(fn (OrderItem $i) => [
                'name' => "{$i->product_name} ({$i->variant_name})",
                'quantity' => $i->quantity,
                'unitPriceCents' => $i->unit_price_cents,
                'weightGrams' => $i->weight_grams,
            ])->values()->all(),
        ];
    }

    public function page(string $number): ?array
    {
        $order = Order::query()->with(['items', 'events'])->where('number', $number)->first();
        if ($order === null) {
            return null;
        }

        return [
            'number' => $order->number,
            'status' => $order->status->value,
            'memberId' => $order->member_id,
            'createdAt' => $order->created_at->toIso8601String(),
            'paidAt' => $order->paid_at?->toIso8601String(),
            'customer' => [
                'name' => $order->customer_name,
                'email' => $order->customer_email,
                'cpf' => $order->customer_cpf === null ? null : Cpf::mask($order->customer_cpf),
            ],
            'pickup' => $order->pickup,
            'address' => $order->address,
            'shipping' => $order->pickup ? null : [
                'carrier' => $order->shipping_carrier,
                'service' => $order->shipping_service,
                'days' => $order->shipping_days,
            ],
            'tracking' => $order->tracking_code === null ? null : ['code' => $order->tracking_code, 'url' => $order->tracking_url],
            'items' => $order->items->map(fn (OrderItem $i) => [
                'name' => $i->product_name,
                'variant' => $i->variant_name,
                'quantity' => $i->quantity,
                'unitPriceCents' => $i->unit_price_cents,
                'lineCents' => $i->line_cents,
                'madeToOrder' => $i->made_to_order,
                'productionDays' => $i->production_days,
            ])->values()->all(),
            'subtotalCents' => $order->subtotal_cents,
            'shippingCents' => $order->shipping_cents,
            'totalCents' => $order->total_cents,
            'events' => $order->events->map(fn (OrderEvent $e) => [
                'from' => $e->from_status,
                'to' => $e->to_status,
                'actor' => $e->actor,
                'note' => $e->actor === 'customer' || $e->actor === 'system' ? $e->note : null,
                'at' => $e->created_at->toIso8601String(),
            ])->values()->all(),
        ];
    }

    public function ofMember(int $memberId): array
    {
        return Order::query()
            ->where('member_id', $memberId)
            ->withSum('items', 'quantity')
            ->latest('id')
            ->get()
            ->map(fn (Order $o) => [
                'number' => $o->number,
                'status' => $o->status->value,
                'totalCents' => $o->total_cents,
                'createdAt' => $o->created_at->toIso8601String(),
                'items' => (int) $o->getAttribute('items_sum_quantity'),
            ])
            ->values()
            ->all();
    }

    public function anonymizeFor(int $memberId, string $email, int $retentionYears): void
    {
        Order::query()
            ->where(fn ($q) => $q->where('member_id', $memberId)->orWhere('customer_email', mb_strtolower($email)))
            ->get()
            ->each(function (Order $order) use ($retentionYears) {
                $address = $order->address;
                $order->update([
                    'member_id' => null,
                    'customer_name' => self::DELETED_HOLDER,
                    'customer_email' => null,
                    'customer_phone' => null,
                    'address' => $address === null ? null : ['city' => $address['city'], 'state' => $address['state']],
                    'retention_until' => $order->paid_at?->copy()->addYears($retentionYears) ?? now(),
                ]);
            });
    }

    public function purgeRetainedUntil(DateTimeInterface $now): int
    {
        $due = Order::query()->whereNotNull('retention_until')->where('retention_until', '<=', $now)->pluck('id');
        // Items and the order diary go with the order (cascade); stock movements keep their numbers.
        Order::query()->whereKey($due)->delete();

        return $due->count();
    }

    /** @return array{id: int, number: string, status: OrderStatus, totalCents: int, paymentOrderId: ?string, memberId: ?int, cartToken: ?string, pickup: bool, shipmentId: ?string} */
    private function snapshot(Order $order): array
    {
        return [
            'id' => $order->id,
            'number' => $order->number,
            'status' => $order->status,
            'totalCents' => $order->total_cents,
            'paymentOrderId' => $order->payment_order_id,
            'memberId' => $order->member_id,
            'cartToken' => $order->cart_token,
            'pickup' => $order->pickup,
            'shipmentId' => $order->shipment_id,
        ];
    }

    /** Next number of the year, under a row lock (two checkouts never share a number). */
    private function nextNumber(int $year): string
    {
        DB::table('order_sequences')->insertOrIgnore(['year' => $year, 'last' => 0]);
        $last = (int) DB::table('order_sequences')->where('year', $year)->lockForUpdate()->value('last') + 1;
        DB::table('order_sequences')->where('year', $year)->update(['last' => $last]);

        return OrderNumber::format($year, $last);
    }

    private function event(int $orderId, ?OrderStatus $from, OrderStatus $to, string $actor, ?int $actorId, ?string $note): void
    {
        OrderEvent::query()->create([
            'order_id' => $orderId,
            'from_status' => $from?->value,
            'to_status' => $to->value,
            'actor' => $actor,
            'actor_id' => $actorId,
            'note' => $note,
        ]);
    }
}

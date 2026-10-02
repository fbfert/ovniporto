<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Catalog\Money;
use App\Domain\Orders\Contracts\OrderAdminRepository;
use App\Domain\Orders\Contracts\OrderRepository;
use App\Domain\Orders\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;

final readonly class EloquentOrderAdminRepository implements OrderAdminRepository
{
    public function __construct(private OrderRepository $orders) {}

    public function search(?string $status, ?string $query, int $page, int $perPage): array
    {
        $base = Order::query()
            ->when($query !== null && $query !== '', function (Builder $q) use ($query) {
                $like = '%'.addcslashes((string) $query, '%_\\').'%';
                $q->where(fn (Builder $w) => $w->where('number', 'like', $like)
                    ->orWhere('customer_name', 'like', $like)
                    ->orWhere('customer_email', 'like', $like));
            });

        $counts = array_fill_keys(array_map(fn (OrderStatus $s) => $s->value, OrderStatus::cases()), 0);
        (clone $base)->selectRaw('status, count(*) as total')->groupBy('status')->toBase()->get()
            ->each(function (object $row) use (&$counts) {
                /** @var object{status: string, total: int|string} $row */
                $counts[$row->status] = (int) $row->total;
            });

        $filtered = (clone $base)->when($status !== null, fn (Builder $q) => $q->where('status', $status));

        return [
            'items' => (clone $filtered)
                ->withSum('items', 'quantity')
                ->orderByRaw($status === OrderStatus::Paid->value ? 'coalesce(paid_at, created_at) asc' : 'id desc')
                ->forPage(max(1, $page), $perPage)
                ->get()
                ->map(fn (Order $o) => [
                    'number' => $o->number,
                    'status' => $o->status->value,
                    'customer' => $o->customer_name,
                    'items' => (int) $o->getAttribute('items_sum_quantity'),
                    'totalCents' => $o->total_cents,
                    'payment' => $this->paymentMethod($o),
                    'pickup' => $o->pickup,
                    'createdAt' => $o->created_at->toIso8601String(),
                    'paidAt' => $o->paid_at?->toIso8601String(),
                ])
                ->values()
                ->all(),
            'total' => (clone $filtered)->count(),
            'counts' => $counts,
        ];
    }

    public function sheet(string $number): ?array
    {
        $page = $this->orders->page($number);
        $order = Order::query()->with('items')->where('number', $number)->first();
        if ($page === null || $order === null) {
            return null;
        }

        return [
            ...$page,
            'customer' => [...$page['customer'], 'phone' => $order->customer_phone],
            'payment' => [
                'method' => $this->paymentMethod($order),
                'orderId' => $order->payment_order_id,
                'captureId' => $order->payment_capture_id,
            ],
            'shipmentId' => $order->shipment_id,
            'items' => $order->items->map(fn (OrderItem $i) => [
                'name' => $i->product_name,
                'variant' => $i->variant_name,
                'sku' => $i->sku,
                'quantity' => $i->quantity,
                'unitPriceCents' => $i->unit_price_cents,
                'lineCents' => $i->line_cents,
                'madeToOrder' => $i->made_to_order,
                'productionDays' => $i->production_days,
            ])->values()->all(),
            'events' => array_map(fn (array $e) => [...$e, 'note' => $e['note'] ?? null], $this->allEvents($order)),
        ];
    }

    public function cpfOf(string $number): ?string
    {
        return Order::query()->where('number', $number)->first()?->customer_cpf;
    }

    public function captureIdOf(string $number): ?string
    {
        return Order::query()->where('number', $number)->value('payment_capture_id');
    }

    public function exportRows(DateTimeInterface $from, DateTimeInterface $to): array
    {
        return Order::query()
            ->with('items')
            ->whereBetween('created_at', [$from, $to])
            ->orderBy('id')
            ->get()
            ->map(fn (Order $o) => [
                'numero' => $o->number,
                'data' => $o->created_at->format('Y-m-d H:i'),
                'status' => $o->status->value,
                'cliente' => (string) $o->customer_name,
                'email' => (string) $o->customer_email,
                'itens' => $o->items->map(fn (OrderItem $i) => "{$i->quantity}x {$i->product_name} ({$i->variant_name})")->implode('; '),
                'subtotal' => Money::cents($o->subtotal_cents)->decimal(),
                'frete' => Money::cents($o->shipping_cents)->decimal(),
                'total' => Money::cents($o->total_cents)->decimal(),
                'entrega' => $o->pickup ? 'Retirada em Lages' : trim(($o->address['city'] ?? '').'/'.($o->address['state'] ?? ''), '/'),
                'rastreio' => (string) $o->tracking_code,
            ])
            ->values()
            ->all();
    }

    public function setTracking(string $number, string $code, ?string $url): void
    {
        Order::query()->where('number', $number)->update(['tracking_code' => $code, 'tracking_url' => $url]);
    }

    private function paymentMethod(Order $order): ?string
    {
        if ($order->payment_order_id === null) {
            return null;
        }

        return str_starts_with($order->payment_order_id, 'SIM-') ? 'Simulado' : 'PayPal';
    }

    /** @return list<array{from: ?string, to: string, actor: string, note: ?string, at: string}> panel sees every note */
    private function allEvents(Order $order): array
    {
        return $order->events()->get()->map(fn ($e) => [
            'from' => $e->from_status,
            'to' => $e->to_status,
            'actor' => $e->actor,
            'note' => $e->note,
            'at' => $e->created_at->toIso8601String(),
        ])->values()->all();
    }
}

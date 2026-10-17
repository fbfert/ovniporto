<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Orders\Contracts\ReturnRequestRepository;
use App\Domain\Orders\OrderStatus;
use App\Domain\Orders\ReturnReason;
use App\Domain\Orders\ReturnRequestStatus;
use App\Models\Order;
use App\Models\OrderEvent;
use App\Models\ReturnRequest;
use App\Models\ReturnRequestEvent;
use DateTimeImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

final class EloquentReturnRequestRepository implements ReturnRequestRepository
{
    public function orderForReturn(string $orderNumber): ?array
    {
        $order = Order::query()->where('number', $orderNumber)->first();
        if ($order === null) {
            return null;
        }
        $delivered = OrderEvent::query()
            ->where('order_id', $order->id)
            ->where('to_status', OrderStatus::Delivered->value)
            ->latest('id')
            ->value('created_at');

        return [
            'id' => $order->id,
            'email' => $order->customer_email,
            'status' => $order->status,
            'deliveredAt' => $delivered === null ? null : new DateTimeImmutable((string) $delivered),
        ];
    }

    public function create(
        int $orderId,
        string $name,
        string $email,
        ReturnReason $reason,
        string $message,
        bool $withinWithdrawal,
        ?DateTimeImmutable $deliveredAt,
    ): string {
        return DB::transaction(function () use ($orderId, $name, $email, $reason, $message, $withinWithdrawal, $deliveredAt) {
            $request = ReturnRequest::query()->create([
                'protocol' => $this->nextProtocol((int) now()->format('Y')),
                'order_id' => $orderId,
                'name' => $name,
                'email' => $email,
                'reason' => $reason,
                'message' => $message,
                'within_withdrawal' => $withinWithdrawal,
                'delivered_at' => $deliveredAt,
                'status' => ReturnRequestStatus::Received,
            ]);
            ReturnRequestEvent::query()->create(['return_request_id' => $request->id, 'to_status' => ReturnRequestStatus::Received->value]);

            return $request->protocol;
        });
    }

    public function list(?string $search, ?ReturnRequestStatus $status, int $limit): array
    {
        $base = ReturnRequest::query()
            ->join('orders', 'orders.id', '=', 'return_requests.order_id')
            ->select('return_requests.*', 'orders.number as order_number')
            ->when($search !== null && $search !== '', function (Builder $q) use ($search) {
                $like = '%'.addcslashes((string) $search, '%_\\').'%';
                $q->where(fn (Builder $w) => $w->where('return_requests.protocol', 'like', $like)
                    ->orWhere('orders.number', 'like', $like)
                    ->orWhere('return_requests.name', 'like', $like)
                    ->orWhere('return_requests.email', 'like', $like));
            });

        $counts = array_fill_keys(array_map(fn (ReturnRequestStatus $s) => $s->value, ReturnRequestStatus::cases()), 0);
        (clone $base)->reorder()->selectRaw('return_requests.status as status, count(*) as total')->groupBy('return_requests.status')->toBase()->get()
            ->each(function (object $row) use (&$counts) {
                /** @var object{status: string, total: int|string} $row */
                $counts[$row->status] = (int) $row->total;
            });

        return [
            'items' => (clone $base)
                ->when($status !== null, fn (Builder $q) => $q->where('return_requests.status', $status?->value))
                ->orderByDesc('return_requests.id')
                ->limit($limit)
                ->get()
                ->map(fn (ReturnRequest $r) => [
                    'protocol' => $r->protocol,
                    'orderNumber' => (string) $r->getAttribute('order_number'),
                    'reason' => $r->reason->value,
                    'withinWithdrawal' => $r->within_withdrawal,
                    'status' => $r->status->value,
                    'createdAt' => $r->created_at->toIso8601String(),
                ])
                ->values()
                ->all(),
            'counts' => $counts,
        ];
    }

    public function sheet(string $protocol): ?array
    {
        $request = ReturnRequest::query()->with(['order', 'events.actor'])->where('protocol', $protocol)->first();
        if ($request === null) {
            return null;
        }

        return [
            'protocol' => $request->protocol,
            'status' => $request->status->value,
            'next' => array_map(fn (ReturnRequestStatus $s) => $s->value, $request->status->next()),
            'reason' => $request->reason->value,
            'name' => $request->name,
            'email' => $request->email,
            'message' => $request->message,
            'withinWithdrawal' => $request->within_withdrawal,
            'deliveredAt' => $request->delivered_at?->toIso8601String(),
            'createdAt' => $request->created_at->toIso8601String(),
            'order' => [
                'number' => $request->order->number,
                'status' => $request->order->status->value,
                'totalCents' => $request->order->total_cents,
            ],
            'events' => $request->events->map(fn (ReturnRequestEvent $e) => [
                'from' => $e->from_status,
                'to' => $e->to_status,
                'actor' => $e->actor?->nickname,
                'note' => $e->note,
                'at' => $e->created_at->toIso8601String(),
            ])->values()->all(),
        ];
    }

    public function statusOf(string $protocol): ?array
    {
        $request = ReturnRequest::query()->where('protocol', $protocol)->first(['id', 'status']);

        return $request === null ? null : ['id' => $request->id, 'status' => $request->status];
    }

    public function changeStatus(int $id, ReturnRequestStatus $from, ReturnRequestStatus $to, int $actorId, string $note): void
    {
        DB::transaction(function () use ($id, $from, $to, $actorId, $note) {
            ReturnRequest::query()->whereKey($id)->update(['status' => $to, 'updated_at' => now()]);
            ReturnRequestEvent::query()->create([
                'return_request_id' => $id,
                'from_status' => $from->value,
                'to_status' => $to->value,
                'actor_id' => $actorId,
                'note' => $note,
            ]);
        });
    }

    public function waiting(int $limit): array
    {
        return ReturnRequest::query()
            ->with('order:id,number')
            ->where('status', ReturnRequestStatus::Received)
            ->orderBy('id')
            ->limit($limit)
            ->get()
            ->map(fn (ReturnRequest $r) => [
                'protocol' => $r->protocol,
                'orderNumber' => $r->order->number,
                'createdAt' => $r->created_at->toIso8601String(),
            ])
            ->values()
            ->all();
    }

    public function ofMember(int $memberId, string $email): array
    {
        return ReturnRequest::query()
            ->with('order:id,number')
            ->whereHas('order', fn (Builder $q) => $q->where('member_id', $memberId)->orWhere('customer_email', mb_strtolower($email)))
            ->orderBy('id')
            ->get()
            ->map(fn (ReturnRequest $r) => [
                'protocolo' => $r->protocol,
                'pedido' => $r->order->number,
                'motivo' => $r->reason->label(),
                'mensagem' => $r->message,
                'dentro_do_prazo_de_arrependimento' => $r->within_withdrawal,
                'status' => $r->status->value,
                'enviado_em' => $r->created_at->toIso8601String(),
            ])
            ->values()
            ->all();
    }

    /** Next protocol of the year, under a row lock (two requests never share one). */
    private function nextProtocol(int $year): string
    {
        DB::table('return_request_sequences')->insertOrIgnore(['year' => $year, 'last' => 0]);
        $last = (int) DB::table('return_request_sequences')->where('year', $year)->lockForUpdate()->value('last') + 1;
        DB::table('return_request_sequences')->where('year', $year)->update(['last' => $last]);

        return sprintf('DEV-%04d-%06d', $year, $last);
    }
}

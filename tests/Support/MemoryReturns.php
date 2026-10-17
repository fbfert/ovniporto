<?php

namespace Tests\Support;

use App\Domain\Orders\Contracts\ReturnNotifier;
use App\Domain\Orders\Contracts\ReturnRequestRepository;
use App\Domain\Orders\OrderStatus;
use App\Domain\Orders\ReturnReason;
use App\Domain\Orders\ReturnRequestStatus;
use DateTimeImmutable;

/** Return requests in memory, for the use case tests; also records the notices it would send. */
final class MemoryReturns implements ReturnNotifier, ReturnRequestRepository
{
    /** @var list<array<string, mixed>> */
    public array $created = [];

    /** @var list<array{protocol: string, email: string}> */
    public array $sent = [];

    /** @var array<string, array{id: int, status: ReturnRequestStatus}> */
    public array $statuses = [];

    /** @var list<array{id: int, from: ReturnRequestStatus, to: ReturnRequestStatus, actorId: int, note: string}> */
    public array $changes = [];

    /** @param array<string, array{id: int, email: ?string, status: OrderStatus, deliveredAt: ?DateTimeImmutable}> $orders */
    public function __construct(private readonly array $orders = []) {}

    public function orderForReturn(string $orderNumber): ?array
    {
        return $this->orders[$orderNumber] ?? null;
    }

    public function create(int $orderId, string $name, string $email, ReturnReason $reason, string $message, bool $withinWithdrawal, ?DateTimeImmutable $deliveredAt): string
    {
        $this->created[] = compact('orderId', 'name', 'email', 'reason', 'message', 'withinWithdrawal', 'deliveredAt');
        $protocol = sprintf('DEV-2026-%06d', count($this->created));
        $this->statuses[$protocol] = ['id' => count($this->created), 'status' => ReturnRequestStatus::Received];

        return $protocol;
    }

    public function list(?string $search, ?ReturnRequestStatus $status, int $limit): array
    {
        return ['items' => [], 'counts' => []];
    }

    public function sheet(string $protocol): ?array
    {
        return null;
    }

    public function statusOf(string $protocol): ?array
    {
        return $this->statuses[$protocol] ?? null;
    }

    public function changeStatus(int $id, ReturnRequestStatus $from, ReturnRequestStatus $to, int $actorId, string $note): void
    {
        $this->changes[] = compact('id', 'from', 'to', 'actorId', 'note');
        foreach ($this->statuses as $protocol => $row) {
            if ($row['id'] === $id) {
                $this->statuses[$protocol]['status'] = $to;
            }
        }
    }

    public function waiting(int $limit): array
    {
        return [];
    }

    public function ofMember(int $memberId, string $email): array
    {
        return [];
    }

    public function received(string $protocol, string $buyerEmail): void
    {
        $this->sent[] = ['protocol' => $protocol, 'email' => $buyerEmail];
    }
}

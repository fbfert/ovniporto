<?php

namespace App\Domain\Orders\Contracts;

use App\Domain\Orders\Data\NewOrder;
use App\Domain\Orders\OrderStatus;

/**
 * Orders and their status history. A snapshot is the small set of fields the
 * use cases decide on: id, number, status, totalCents, paymentOrderId,
 * memberId, cartToken, pickup, shipmentId.
 */
interface OrderRepository
{
    /**
     * Creates the order in pending_payment with its number and first event.
     *
     * @return array{id: int, number: string}
     */
    public function create(NewOrder $order): array;

    /** @return array{id: int, number: string, status: OrderStatus, totalCents: int, paymentOrderId: ?string, memberId: ?int, cartToken: ?string, pickup: bool, shipmentId: ?string}|null */
    public function findByNumber(string $number): ?array;

    /** @return array{id: int, number: string, status: OrderStatus, totalCents: int, paymentOrderId: ?string, memberId: ?int, cartToken: ?string, pickup: bool, shipmentId: ?string}|null */
    public function findByPaymentOrderId(string $paymentOrderId): ?array;

    public function setPaymentOrderId(int $orderId, string $paymentOrderId): void;

    /**
     * Runs $work in a transaction holding a row lock on the order, with its fresh snapshot.
     *
     * @template T
     *
     * @param  callable(array{id: int, number: string, status: OrderStatus, totalCents: int, paymentOrderId: ?string, memberId: ?int, cartToken: ?string, pickup: bool, shipmentId: ?string}): T  $work
     * @return T
     */
    public function locked(int $orderId, callable $work): mixed;

    /** @param array<string, mixed> $changes extra columns (paid_at, payment_capture_id, tracking…) */
    public function changeStatus(int $orderId, OrderStatus $from, OrderStatus $to, string $actor, ?int $actorId, ?string $note, array $changes = []): void;

    public function addNote(int $orderId, OrderStatus $status, string $actor, string $note): void;

    /** Stock leaves the shelf only for in-stock items, and only once (called inside the paid transition). */
    public function decrementStock(int $orderId): void;

    /** @return list<int> pending_payment orders created before the cutoff */
    public function abandonedBefore(\DateTimeInterface $cutoff): array;

    /** @return list<array{id: int, shipmentId: string}> shipped orders with a provider shipment */
    public function inTransit(): array;

    public function setShipment(int $orderId, string $shipmentId, ?string $trackingCode, ?string $trackingUrl): void;

    /** @return array<string, mixed>|null everything the label purchase needs (address, recipient, items) */
    public function shipmentData(int $orderId): ?array;

    /** @return array<string, mixed>|null the order page: timeline, items, address (CPF masked) */
    public function page(string $number): ?array;

    /** @return list<array{number: string, status: string, totalCents: int, createdAt: string, items: int}> newest first */
    public function ofMember(int $memberId): array;

    /** Account deleted: keeps what tax law needs (items, totals, CPF, city) and drops name, contact and street. */
    public function anonymizeFor(int $memberId): void;
}

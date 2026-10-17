<?php

namespace App\Domain\Orders\Contracts;

use App\Domain\Orders\OrderStatus;
use App\Domain\Orders\ReturnReason;
use App\Domain\Orders\ReturnRequestStatus;
use DateTimeImmutable;

/**
 * Return and withdrawal requests. Each one belongs to an order and follows its retention: the
 * order's anonymization clears name, e-mail and message, its purge deletes the request.
 */
interface ReturnRequestRepository
{
    /**
     * What the form checks before accepting a request: the order's e-mail, status and the day it
     * was marked delivered (null while not delivered).
     *
     * @return array{id: int, email: ?string, status: OrderStatus, deliveredAt: ?DateTimeImmutable}|null
     */
    public function orderForReturn(string $orderNumber): ?array;

    /** Stores a received request and returns its protocol, "DEV-2026-000001". */
    public function create(
        int $orderId,
        string $name,
        string $email,
        ReturnReason $reason,
        string $message,
        bool $withinWithdrawal,
        ?DateTimeImmutable $deliveredAt,
    ): string;

    /**
     * The panel list, newest first (at most $limit), with how many requests each status has.
     *
     * @return array{items: list<array{protocol: string, orderNumber: string, reason: string, withinWithdrawal: bool, status: string, createdAt: string}>, counts: array<string, int>}
     */
    public function list(?string $search, ?ReturnRequestStatus $status, int $limit): array;

    /** @return array<string, mixed>|null the sheet: request, order link and history */
    public function sheet(string $protocol): ?array;

    /** @return array{id: int, status: ReturnRequestStatus}|null */
    public function statusOf(string $protocol): ?array;

    public function changeStatus(int $id, ReturnRequestStatus $from, ReturnRequestStatus $to, int $actorId, string $note): void;

    /** @return list<array{protocol: string, orderNumber: string, createdAt: string}> received and not handled yet, oldest first */
    public function waiting(int $limit): array;

    /**
     * For "Baixar meus dados": requests on the orders placed signed in or with the account e-mail.
     *
     * @return list<array<string, mixed>>
     */
    public function ofMember(int $memberId, string $email): array;
}

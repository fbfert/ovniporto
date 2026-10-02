<?php

namespace App\Domain\Orders\Contracts;

use DateTimeInterface;

/** Orders as the store role sees them: lists, the full record, the CPF on request and exports. */
interface OrderAdminRepository
{
    /** @return array{items: list<array<string, mixed>>, total: int, counts: array<string, int>} */
    public function search(?string $status, ?string $query, int $page, int $perPage): array;

    /** @return array<string, mixed>|null everything on the order sheet, CPF masked */
    public function sheet(string $number): ?array;

    public function cpfOf(string $number): ?string;

    public function captureIdOf(string $number): ?string;

    /** @return list<array<string, string>> one row per order created in the period */
    public function exportRows(DateTimeInterface $from, DateTimeInterface $to): array;

    public function setTracking(string $number, string $code, ?string $url): void;
}

<?php

namespace App\Domain\Audit\Contracts;

use App\Domain\Audit\Data\AuditEntry;

/**
 * Wraps a panel action: the change and its audit record are written in the
 * same transaction, with the object's state before and after.
 */
interface Auditor
{
    /**
     * @template T
     *
     * @param  callable(): (array<string, mixed>|null)  $snapshot  the subject's current state
     * @param  callable(): T  $change
     * @return T
     */
    public function audited(AuditEntry $entry, callable $snapshot, callable $change): mixed;

    /**
     * Oldest first.
     *
     * @return list<array{action: string, actor: ?string, context: array<string, mixed>, at: string}>
     */
    public function history(string $subjectType, int $subjectId): array;

    /**
     * Newest first, for the admin's audit screen.
     *
     * @return array{items: list<array{id: int, action: string, actor: ?string, subjectType: string, subjectId: int, before: ?array<string, mixed>, after: ?array<string, mixed>, context: array<string, mixed>, at: string}>, hasMore: bool}
     */
    public function recent(int $page, int $perPage): array;
}

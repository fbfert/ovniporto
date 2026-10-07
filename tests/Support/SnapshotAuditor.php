<?php

namespace Tests\Support;

use App\Domain\Audit\Contracts\Auditor;
use App\Domain\Audit\Data\AuditEntry;

/** Records each entry with the before and after snapshots, as the database auditor would. */
final class SnapshotAuditor implements Auditor
{
    /** @var list<array{entry: AuditEntry, before: mixed, after: mixed}> */
    public array $records = [];

    public function audited(AuditEntry $entry, callable $snapshot, callable $change): mixed
    {
        $before = $snapshot();
        $result = $change();
        $this->records[] = ['entry' => $entry, 'before' => $before, 'after' => $snapshot()];

        return $result;
    }

    public function history(string $subjectType, int $subjectId): array
    {
        return [];
    }

    public function recent(int $page, int $perPage): array
    {
        return ['items' => [], 'hasMore' => false];
    }
}

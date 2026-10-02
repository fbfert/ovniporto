<?php

namespace App\Infrastructure\Audit;

use App\Domain\Audit\Contracts\Auditor;
use App\Domain\Audit\Data\AuditEntry;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;

final class DatabaseAuditor implements Auditor
{
    public function audited(AuditEntry $entry, callable $snapshot, callable $change): mixed
    {
        return DB::transaction(function () use ($entry, $snapshot, $change) {
            $before = $snapshot();
            $result = $change();

            AuditLog::query()->create([
                'actor_id' => $entry->actorId,
                'action' => $entry->action,
                'subject_type' => $entry->subjectType,
                'subject_id' => $entry->subjectId,
                'before' => $before,
                'after' => $snapshot(),
                'context' => $entry->context === [] ? null : $entry->context,
            ]);

            return $result;
        });
    }

    public function history(string $subjectType, int $subjectId): array
    {
        return AuditLog::query()
            ->with('actor:id,nickname,name')
            ->where('subject_type', $subjectType)
            ->where('subject_id', $subjectId)
            ->oldest('id')
            ->get()
            ->map(fn (AuditLog $log) => [
                'action' => $log->action,
                'actor' => $log->actor === null ? null : ($log->actor->nickname ?? $log->actor->name),
                'context' => $log->context ?? [],
                'at' => $log->created_at->toIso8601String(),
            ])
            ->values()
            ->all();
    }

    public function recent(int $page, int $perPage): array
    {
        $rows = AuditLog::query()
            ->with('actor:id,nickname,name')
            ->latest('id')
            ->skip((max(1, $page) - 1) * $perPage)
            ->take($perPage + 1)
            ->get();

        return [
            'items' => $rows->take($perPage)
                ->map(fn (AuditLog $log) => [
                    'id' => $log->id,
                    'action' => $log->action,
                    'actor' => $log->actor === null ? null : ($log->actor->nickname ?? $log->actor->name),
                    'subjectType' => $log->subject_type,
                    'subjectId' => $log->subject_id,
                    'before' => $log->before,
                    'after' => $log->after,
                    'context' => $log->context ?? [],
                    'at' => $log->created_at->toIso8601String(),
                ])
                ->values()
                ->all(),
            'hasMore' => $rows->count() > $perPage,
        ];
    }
}

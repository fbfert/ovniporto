<?php

use App\Application\Sightings\UseCases\ModerateSighting;
use App\Domain\Audit\Contracts\Auditor;
use App\Domain\Audit\Data\AuditEntry;
use App\Domain\Sightings\Contracts\ModerationRepository;
use App\Domain\Sightings\Contracts\SightingNotifier;
use App\Domain\Sightings\Data\ModerationDecision;
use App\Domain\Sightings\InvalidModeration;
use App\Domain\Sightings\RejectionReason;
use App\Domain\Sightings\SightingStatus;

final class InMemoryModeration implements ModerationRepository
{
    /** @var array<int, array{status: SightingStatus, note: ?string, published: bool}> */
    public array $rows = [];

    public function countByStatus(): array
    {
        return [];
    }

    public function queue(SightingStatus $status, int $page, int $perPage): array
    {
        return [];
    }

    public function review(int $id): ?array
    {
        return null;
    }

    public function neighbours(int $id): array
    {
        return ['previous' => null, 'next' => null];
    }

    public function statusOf(int $id): ?SightingStatus
    {
        return $this->rows[$id]['status'] ?? null;
    }

    public function snapshot(int $id): ?array
    {
        $row = $this->rows[$id] ?? null;

        return $row === null ? null : ['status' => $row['status']->value, 'moderationNote' => $row['note'], 'publishedAt' => $row['published'] ? 'now' : null];
    }

    public function apply(int $id, ModerationDecision $decision, int $moderatorId, DateTimeImmutable $now): void
    {
        $this->rows[$id] = ['status' => $decision->status, 'note' => $decision->noteToAuthor, 'published' => $decision->publish];
    }

    public function oldestPendingSince(): ?DateTimeImmutable
    {
        return null;
    }
}

final class RecordingAuditor implements Auditor
{
    /** @var list<array{entry: AuditEntry, before: mixed, after: mixed}> */
    public array $entries = [];

    public function audited(AuditEntry $entry, callable $snapshot, callable $change): mixed
    {
        $before = $snapshot();
        $result = $change();
        $this->entries[] = ['entry' => $entry, 'before' => $before, 'after' => $snapshot()];

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

final class RecordingNotifier implements SightingNotifier
{
    /** @var list<string> */
    public array $sent = [];

    public function submitted(int $sightingId): void
    {
        $this->sent[] = "submitted:{$sightingId}";
    }

    public function approved(int $sightingId): void
    {
        $this->sent[] = "approved:{$sightingId}";
    }

    public function changesRequested(int $sightingId, string $message): void
    {
        $this->sent[] = "changes:{$sightingId}:{$message}";
    }

    public function rejected(int $sightingId, string $reason): void
    {
        $this->sent[] = "rejected:{$sightingId}:{$reason}";
    }
}

beforeEach(function () {
    $this->repo = new InMemoryModeration;
    $this->repo->rows[7] = ['status' => SightingStatus::Pending, 'note' => null, 'published' => false];
    $this->auditor = new RecordingAuditor;
    $this->notifier = new RecordingNotifier;
    $this->moderate = new ModerateSighting($this->repo, $this->notifier, $this->auditor);
});

it('approves, audits before and after, then tells the author', function () {
    $this->moderate->approve(3, 7);

    expect($this->repo->rows[7]['status'])->toBe(SightingStatus::Approved)
        ->and($this->repo->rows[7]['published'])->toBeTrue()
        ->and($this->auditor->entries[0]['entry']->actorId)->toBe(3)
        ->and($this->auditor->entries[0]['entry']->action)->toBe('sighting.approved')
        ->and($this->auditor->entries[0]['before']['status'])->toBe('pending')
        ->and($this->auditor->entries[0]['after']['status'])->toBe('approved')
        ->and($this->notifier->sent)->toBe(['approved:7']);
});

it('sends the adjustment message to the author', function () {
    $this->moderate->requestChanges(3, 7, 'Tire a placa do carro.');

    expect($this->repo->rows[7]['status'])->toBe(SightingStatus::ChangesRequested)
        ->and($this->notifier->sent)->toBe(['changes:7:Tire a placa do carro.']);
});

it('rejects with the reason in the audit context and the e-mail', function () {
    $this->moderate->reject(3, 7, RejectionReason::IdentifiablePerson, null);

    expect($this->auditor->entries[0]['entry']->context['reason'])->toBe('identifiable_person')
        ->and($this->notifier->sent)->toBe(['rejected:7:Foto com pessoa identificável']);
});

it('unpublishes silently, keeping the note in the audit only', function () {
    $this->repo->rows[7] = ['status' => SightingStatus::Approved, 'note' => null, 'published' => true];

    $this->moderate->unpublish(3, 7, 'Ponto parece residência.');

    expect($this->repo->rows[7]['status'])->toBe(SightingStatus::Pending)
        ->and($this->repo->rows[7]['note'])->toBeNull()
        ->and($this->auditor->entries[0]['entry']->context['note'])->toBe('Ponto parece residência.')
        ->and($this->notifier->sent)->toBe([]);
});

it('changes nothing and sends nothing when the rule refuses', function () {
    $this->repo->rows[7]['status'] = SightingStatus::Rejected;

    expect(fn () => $this->moderate->approve(3, 7))->toThrow(InvalidModeration::class);
    expect($this->auditor->entries)->toBe([])->and($this->notifier->sent)->toBe([]);
});

it('refuses an unknown report', function () {
    expect(fn () => $this->moderate->approve(3, 99))->toThrow(InvalidModeration::class, 'Relato não encontrado.');
});

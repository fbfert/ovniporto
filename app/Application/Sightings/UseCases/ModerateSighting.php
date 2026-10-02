<?php

namespace App\Application\Sightings\UseCases;

use App\Domain\Audit\Contracts\Auditor;
use App\Domain\Audit\Data\AuditEntry;
use App\Domain\Sightings\Contracts\ModerationRepository;
use App\Domain\Sightings\Contracts\SightingNotifier;
use App\Domain\Sightings\Data\ModerationDecision;
use App\Domain\Sightings\InvalidModeration;
use App\Domain\Sightings\ModerationRules;
use App\Domain\Sightings\RejectionReason;
use App\Domain\Sightings\SightingStatus;
use Closure;
use DateTimeImmutable;

/**
 * The tower's four decisions. Each one checks the state machine, writes the
 * change and its audit record together, then queues the author's e-mail.
 * The public caches follow on their own (the report's observer bumps them).
 */
final readonly class ModerateSighting
{
    public function __construct(
        private ModerationRepository $sightings,
        private SightingNotifier $notifier,
        private Auditor $auditor,
    ) {}

    public function approve(int $moderatorId, int $sightingId): void
    {
        $this->decide($moderatorId, $sightingId, 'sighting.approved', fn (SightingStatus $from) => ModerationRules::approve($from));
        $this->notifier->approved($sightingId);
    }

    public function requestChanges(int $moderatorId, int $sightingId, string $message): void
    {
        $decision = $this->decide(
            $moderatorId,
            $sightingId,
            'sighting.changes_requested',
            fn (SightingStatus $from) => ModerationRules::requestChanges($from, $message),
        );
        $this->notifier->changesRequested($sightingId, (string) $decision->noteToAuthor);
    }

    public function reject(int $moderatorId, int $sightingId, RejectionReason $reason, ?string $detail): void
    {
        $decision = $this->decide(
            $moderatorId,
            $sightingId,
            'sighting.rejected',
            fn (SightingStatus $from) => ModerationRules::reject($from, $reason, $detail),
            ['reason' => $reason->value],
        );
        $this->notifier->rejected($sightingId, (string) $decision->noteToAuthor);
    }

    public function unpublish(int $moderatorId, int $sightingId, string $note): void
    {
        $this->decide(
            $moderatorId,
            $sightingId,
            'sighting.unpublished',
            fn (SightingStatus $from) => ModerationRules::unpublish($from, $note),
            ['note' => trim($note)],
        );
    }

    /**
     * @param  Closure(SightingStatus): ModerationDecision  $rule
     * @param  array<string, mixed>  $context
     */
    private function decide(int $moderatorId, int $sightingId, string $action, Closure $rule, array $context = []): ModerationDecision
    {
        $from = $this->sightings->statusOf($sightingId)
            ?? throw new InvalidModeration('status', 'Relato não encontrado.');
        $decision = $rule($from);
        if ($decision->noteToAuthor !== null) {
            $context['noteToAuthor'] = $decision->noteToAuthor;
        }

        $this->auditor->audited(
            new AuditEntry($moderatorId, $action, 'sighting', $sightingId, $context),
            fn () => $this->sightings->snapshot($sightingId),
            fn () => $this->sightings->apply($sightingId, $decision, $moderatorId, new DateTimeImmutable),
        );

        return $decision;
    }
}

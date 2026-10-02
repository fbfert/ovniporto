<?php

namespace App\Application\Campaign\UseCases;

use App\Domain\Audit\Contracts\Auditor;
use App\Domain\Audit\Data\AuditEntry;
use App\Domain\Campaign\Contracts\WaitlistRepository;

/** /painel/avise-me: who asked to be told when the campaign opens. */
final readonly class ManageWaitlist
{
    public const CSV_HEADER = ['email', 'origem', 'consentiu_em', 'confirmou_em'];

    public function __construct(
        private WaitlistRepository $subscribers,
        private Auditor $auditor,
    ) {}

    /** @return array{subscribers: list<array<string, mixed>>, confirmed: int} */
    public function page(): array
    {
        $subscribers = $this->subscribers->subscribers();

        return [
            'subscribers' => $subscribers,
            'confirmed' => count(array_filter($subscribers, fn (array $s) => $s['confirmedAt'] !== null)),
        ];
    }

    /** @return list<list<string>> rows for the CSV, header first */
    public function export(): array
    {
        return [
            self::CSV_HEADER,
            ...array_map(fn (array $s) => [$s['email'], $s['source'], $s['consentedAt'], $s['confirmedAt'] ?? ''], $this->subscribers->subscribers()),
        ];
    }

    public function remove(int $actorId, int $id): void
    {
        // The e-mail itself does not go into the audit log: removing it is the point.
        $this->auditor->audited(
            new AuditEntry($actorId, 'waitlist.removed', 'waitlist', $id),
            fn () => null,
            fn () => $this->subscribers->remove($id),
        );
    }
}

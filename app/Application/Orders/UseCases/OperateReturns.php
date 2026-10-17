<?php

namespace App\Application\Orders\UseCases;

use App\Domain\Audit\Contracts\Auditor;
use App\Domain\Audit\Data\AuditEntry;
use App\Domain\Orders\Contracts\ReturnRequestRepository;
use App\Domain\Orders\ReturnRequestStatus;
use InvalidArgumentException;

/**
 * "Pedidos → Devoluções" in the panel: the list, the sheet and the status changes. Every change
 * needs a note (what was agreed with the buyer) and goes to the audit. Money is not moved here:
 * the refund stays on the order's own sheet.
 */
final readonly class OperateReturns
{
    public const LIST_LIMIT = 100;

    public function __construct(
        private ReturnRequestRepository $returns,
        private Auditor $auditor,
    ) {}

    /** @return array{items: list<array<string, mixed>>, counts: array<string, int>} */
    public function list(?string $search, ?string $status): array
    {
        return $this->returns->list($search === null ? null : trim($search), ReturnRequestStatus::tryFrom((string) $status), self::LIST_LIMIT);
    }

    /** @return array<string, mixed>|null */
    public function sheet(string $protocol): ?array
    {
        return $this->returns->sheet($protocol);
    }

    public function changeStatus(int $actorId, string $protocol, string $to, string $note): void
    {
        $note = trim($note);
        if (mb_strlen($note) < 3) {
            throw new InvalidArgumentException('Escreva uma nota: o que foi combinado ou feito.');
        }
        $current = $this->returns->statusOf($protocol) ?? throw new InvalidArgumentException('Pedido de devolução não encontrado.');
        $target = ReturnRequestStatus::tryFrom($to) ?? throw new InvalidArgumentException('Status desconhecido.');
        $current['status']->assertCanMoveTo($target);

        $this->auditor->audited(
            new AuditEntry($actorId, 'return.status_changed', 'return_request', $current['id'], [
                'protocol' => $protocol, 'from' => $current['status']->value, 'to' => $target->value, 'note' => $note,
            ]),
            fn () => null,
            fn () => $this->returns->changeStatus($current['id'], $current['status'], $target, $actorId, $note),
        );
    }
}

<?php

namespace App\Domain\Audit\Data;

/** Who did what to which object, plus anything the action carried (a note, a reason). */
final readonly class AuditEntry
{
    /** @param array<string, mixed> $context */
    public function __construct(
        public ?int $actorId,
        public string $action,
        public string $subjectType,
        public int $subjectId,
        public array $context = [],
    ) {}
}

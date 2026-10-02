<?php

namespace App\Domain\Members\Contracts;

/** Port other modules implement to contribute their part of "Baixar meus dados". */
interface MemberDataSource
{
    /** Section name in the export, e.g. "relatos". */
    public function section(): string;

    /** @return array<int|string, mixed> */
    public function exportFor(int $memberId, string $email): array;
}

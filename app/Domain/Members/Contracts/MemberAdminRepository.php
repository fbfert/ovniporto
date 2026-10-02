<?php

namespace App\Domain\Members\Contracts;

use App\Domain\Members\Data\MemberSearch;
use App\Domain\Members\MemberRole;
use DateTimeInterface;

/** Members as the panel sees them: real data, roles and blocks. */
interface MemberAdminRepository
{
    /** @return array{items: list<array<string, mixed>>, total: int} */
    public function search(MemberSearch $search, int $page, int $perPage): array;

    /** @return array<string, mixed>|null profile, role, block and report counts */
    public function detail(int $memberId): ?array;

    public function roleOf(int $memberId): ?MemberRole;

    /** @return array{role: string, blockedAt: ?string, blockedReason: ?string}|null */
    public function snapshot(int $memberId): ?array;

    public function setRole(int $memberId, MemberRole $role): void;

    public function block(int $memberId, string $reason, DateTimeInterface $at): void;

    public function unblock(int $memberId): void;
}

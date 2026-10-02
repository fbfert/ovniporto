<?php

namespace App\Application\Members\UseCases;

use App\Domain\Audit\Contracts\Auditor;
use App\Domain\Audit\Data\AuditEntry;
use App\Domain\Members\Contracts\MemberAdminRepository;
use App\Domain\Members\Data\MemberSearch;
use App\Domain\Members\MemberAdministrationRefused;
use App\Domain\Members\MemberAdministrationRules;
use App\Domain\Members\MemberRole;
use DateTimeImmutable;

/** /painel/membros: list, role, block and deletion, every change audited. */
final readonly class AdministerMembers
{
    public const PER_PAGE = 25;

    public function __construct(
        private MemberAdminRepository $members,
        private Auditor $auditor,
        private DeleteAccount $deleteAccount,
    ) {}

    /** @return array{items: list<array<string, mixed>>, total: int, page: int, hasMore: bool} */
    public function list(MemberSearch $search, int $page): array
    {
        $page = max(1, $page);
        $result = $this->members->search($search, $page, self::PER_PAGE);

        return [...$result, 'page' => $page, 'hasMore' => $page * self::PER_PAGE < $result['total']];
    }

    /** @return array{member: array<string, mixed>, history: list<array<string, mixed>>}|null */
    public function detail(int $memberId): ?array
    {
        $member = $this->members->detail($memberId);

        return $member === null ? null : ['member' => $member, 'history' => $this->auditor->history('member', $memberId)];
    }

    public function changeRole(int $actorId, MemberRole $actorRole, int $memberId, MemberRole $role): void
    {
        $this->target($memberId);
        MemberAdministrationRules::changeRole($actorRole, $actorId, $memberId);
        $this->audited($actorId, 'member.role_changed', $memberId, fn () => $this->members->setRole($memberId, $role), ['role' => $role->value]);
    }

    public function block(int $actorId, int $memberId, string $reason): void
    {
        $reason = MemberAdministrationRules::block($actorId, $memberId, $this->target($memberId), $reason);
        $this->audited($actorId, 'member.blocked', $memberId, fn () => $this->members->block($memberId, $reason, new DateTimeImmutable), ['reason' => $reason]);
    }

    public function unblock(int $actorId, int $memberId): void
    {
        $this->target($memberId);
        $this->audited($actorId, 'member.unblocked', $memberId, fn () => $this->members->unblock($memberId));
    }

    public function delete(int $actorId, MemberRole $actorRole, int $memberId): void
    {
        $this->target($memberId);
        MemberAdministrationRules::delete($actorRole, $actorId, $memberId);
        $this->audited($actorId, 'member.deleted', $memberId, fn () => $this->deleteAccount->erase($memberId));
    }

    private function target(int $memberId): MemberRole
    {
        return $this->members->roleOf($memberId)
            ?? throw new MemberAdministrationRefused('member', 'Membro não encontrado.');
    }

    /** @param array<string, mixed> $context */
    private function audited(int $actorId, string $action, int $memberId, callable $change, array $context = []): void
    {
        $this->auditor->audited(
            new AuditEntry($actorId, $action, 'member', $memberId, $context),
            fn () => $this->members->snapshot($memberId),
            $change,
        );
    }
}

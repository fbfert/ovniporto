<?php

namespace App\Domain\Members;

/**
 * What operators may do to members: only an admin changes roles or deletes
 * accounts; nobody acts on their own account from the panel; a member with a
 * panel role loses the role before being blocked; blocking needs a reason.
 */
final class MemberAdministrationRules
{
    public const REASON_MIN = 10;

    public static function changeRole(MemberRole $actorRole, int $actorId, int $targetId): void
    {
        self::adminOnly($actorRole, 'role', 'Só o admin muda papéis.');
        self::notSelf($actorId, $targetId, 'role');
    }

    public static function block(int $actorId, int $targetId, MemberRole $targetRole, string $reason): string
    {
        self::notSelf($actorId, $targetId, 'reason');
        if ($targetRole !== MemberRole::Member) {
            throw new MemberAdministrationRefused('reason', 'Tire o papel no painel antes de bloquear.');
        }
        $reason = trim($reason);
        if (mb_strlen($reason) < self::REASON_MIN) {
            throw new MemberAdministrationRefused('reason', 'Registre o motivo do bloqueio (pelo menos 10 caracteres).');
        }

        return $reason;
    }

    public static function delete(MemberRole $actorRole, int $actorId, int $targetId): void
    {
        self::adminOnly($actorRole, 'delete', 'Só o admin exclui contas pelo painel.');
        self::notSelf($actorId, $targetId, 'delete');
    }

    private static function adminOnly(MemberRole $actorRole, string $field, string $message): void
    {
        if ($actorRole !== MemberRole::Admin) {
            throw new MemberAdministrationRefused($field, $message);
        }
    }

    private static function notSelf(int $actorId, int $targetId, string $field): void
    {
        if ($actorId === $targetId) {
            throw new MemberAdministrationRefused($field, 'Isso não se faz na própria conta pelo painel.');
        }
    }
}

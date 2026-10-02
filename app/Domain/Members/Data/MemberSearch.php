<?php

namespace App\Domain\Members\Data;

use App\Domain\Members\MemberRole;

/** The members list filters, as they come from the URL. */
final readonly class MemberSearch
{
    public const SORTS = ['recentes', 'apelido'];

    public function __construct(
        public ?string $query = null,
        public ?MemberRole $role = null,
        public bool $blockedOnly = false,
        public string $sort = 'recentes',
    ) {}

    public static function from(mixed $query, mixed $role, mixed $status, mixed $sort): self
    {
        $text = is_string($query) ? trim($query) : '';

        return new self(
            query: $text === '' ? null : mb_substr($text, 0, 80),
            role: is_string($role) ? MemberRole::tryFrom($role) : null,
            blockedOnly: $status === 'bloqueados',
            sort: in_array($sort, self::SORTS, true) ? (string) $sort : 'recentes',
        );
    }
}

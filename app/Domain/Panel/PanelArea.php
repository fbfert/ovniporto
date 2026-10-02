<?php

namespace App\Domain\Panel;

use App\Domain\Members\MemberRole;

/**
 * Areas of the operations panel and who may open each one. This is the only
 * place that maps roles to areas: the server checks it on every request, the
 * front end only uses it to hide menu entries.
 */
enum PanelArea: string
{
    case Home = 'inicio';
    case Sightings = 'relatos';
    case Members = 'membros';
    case Orders = 'pedidos';
    case Products = 'produtos';
    case Content = 'conteudo';
    case Audit = 'auditoria';

    public function allows(MemberRole $role): bool
    {
        return match ($role) {
            MemberRole::Admin => true,
            MemberRole::Moderator => in_array($this, [self::Home, self::Sightings, self::Members], true),
            MemberRole::Store => in_array($this, [self::Home, self::Orders, self::Products], true),
            MemberRole::Member => false,
        };
    }

    /** @return list<self> */
    public static function openTo(MemberRole $role): array
    {
        return array_values(array_filter(self::cases(), fn (self $area) => $area->allows($role)));
    }
}

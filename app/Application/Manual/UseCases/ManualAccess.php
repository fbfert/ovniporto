<?php

namespace App\Application\Manual\UseCases;

use App\Domain\Members\MemberRole;
use App\Domain\Panel\PanelArea;

/** A general chapter is for every panel role; an area chapter only for roles that open that area. */
final class ManualAccess
{
    public static function opens(MemberRole $role, ?string $area): bool
    {
        return $area === null ? $role->canOpenPanel() : PanelArea::from($area)->allows($role);
    }
}

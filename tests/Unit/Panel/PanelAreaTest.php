<?php

use App\Domain\Members\MemberRole;
use App\Domain\Panel\PanelArea;

it('maps every role to its areas in one place', function (MemberRole $role, array $areas) {
    expect(array_map(fn (PanelArea $a) => $a->value, PanelArea::openTo($role)))->toBe($areas);
})->with([
    'admin' => [MemberRole::Admin, ['inicio', 'relatos', 'membros', 'pedidos', 'produtos', 'conteudo', 'auditoria']],
    'moderator' => [MemberRole::Moderator, ['inicio', 'relatos', 'membros']],
    'store' => [MemberRole::Store, ['inicio', 'pedidos', 'produtos']],
    'member' => [MemberRole::Member, []],
]);

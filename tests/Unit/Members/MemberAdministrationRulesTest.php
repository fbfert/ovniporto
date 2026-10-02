<?php

use App\Domain\Members\MemberAdministrationRefused;
use App\Domain\Members\MemberAdministrationRules;
use App\Domain\Members\MemberRole;

it('lets only the admin change roles, never their own', function () {
    MemberAdministrationRules::changeRole(MemberRole::Admin, 1, 2);

    expect(fn () => MemberAdministrationRules::changeRole(MemberRole::Moderator, 1, 2))->toThrow(MemberAdministrationRefused::class)
        ->and(fn () => MemberAdministrationRules::changeRole(MemberRole::Admin, 1, 1))->toThrow(MemberAdministrationRefused::class);
});

it('blocks only common members, with a written reason', function () {
    expect(MemberAdministrationRules::block(1, 2, MemberRole::Member, '  Spam de relatos falsos.  '))->toBe('Spam de relatos falsos.');

    expect(fn () => MemberAdministrationRules::block(1, 2, MemberRole::Member, 'curto'))->toThrow(MemberAdministrationRefused::class)
        ->and(fn () => MemberAdministrationRules::block(1, 2, MemberRole::Store, 'Motivo bem escrito aqui.'))->toThrow(MemberAdministrationRefused::class)
        ->and(fn () => MemberAdministrationRules::block(1, 1, MemberRole::Member, 'Motivo bem escrito aqui.'))->toThrow(MemberAdministrationRefused::class);
});

it('lets only the admin delete accounts from the panel', function () {
    MemberAdministrationRules::delete(MemberRole::Admin, 1, 2);

    expect(fn () => MemberAdministrationRules::delete(MemberRole::Moderator, 1, 2))->toThrow(MemberAdministrationRefused::class);
});

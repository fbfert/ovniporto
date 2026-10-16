<?php

use App\Application\Members\UseCases\SignInWithIdentity;
use App\Domain\Members\Data\Identity;
use Tests\Support\InMemoryMembers;

it('creates the member on the first sign-in only', function () {
    $members = new InMemoryMembers;
    $signIn = new SignInWithIdentity($members);
    $identity = new Identity('google-1', 'Ana Souza', 'ana@example.test', null);

    $first = $signIn->execute($identity);
    $second = $signIn->execute(new Identity('google-1', 'Ana S.', 'ana@example.test', null));

    expect($first)->toBe(['memberId' => 1, 'isNew' => true])
        ->and($second)->toBe(['memberId' => 1, 'isNew' => false])
        ->and($members->rows)->toHaveCount(1)
        ->and($members->rows[1]['name'])->toBe('Ana S.');
});

it('records every sign-in as the last access and restarts the inactivity cycle', function () {
    $members = new InMemoryMembers;
    $id = $members->add('ana@example.test', new DateTimeImmutable('-5 years'), noticeAt: new DateTimeImmutable('-10 days'));
    $members->markInactivityAlerted($id, new DateTimeImmutable('-1 day'));

    (new SignInWithIdentity($members))->execute(new Identity("google-{$id}", 'Ana', 'ana@example.test', null));

    expect($members->rows[$id]['lastSeenAt'])->toBeGreaterThan(new DateTimeImmutable('-1 minute'))
        ->and($members->rows[$id]['noticeAt'])->toBeNull()
        ->and($members->rows[$id]['alertedAt'])->toBeNull();
});

it('gives the first sign-in a last access too', function () {
    $members = new InMemoryMembers;

    $result = (new SignInWithIdentity($members))->execute(new Identity('google-9', 'Bia', 'bia@example.test', null));

    expect($members->rows[$result['memberId']]['lastSeenAt'])->not->toBeNull();
});

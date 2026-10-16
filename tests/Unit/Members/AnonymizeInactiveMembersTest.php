<?php

use App\Application\Members\UseCases\AnonymizeInactiveMembers;
use App\Application\Members\UseCases\SignInWithIdentity;
use App\Domain\Members\Contracts\InactivityNotifier;
use App\Domain\Members\Contracts\MemberContentAnonymizer;
use App\Domain\Members\Data\Identity;
use Tests\Support\InMemoryMembers;

/** Records the e-mails of the cycle instead of sending them. */
final class SpyInactivityNotifier implements InactivityNotifier
{
    /** @var list<array{email: string, anonymizeOn: string}> */
    public array $notices = [];

    /** @var list<int> */
    public array $alerts = [];

    public function noticeAnonymization(string $email, ?string $nickname, DateTimeInterface $anonymizeOn): void
    {
        $this->notices[] = ['email' => $email, 'anonymizeOn' => $anonymizeOn->format('Y-m-d')];
    }

    public function alertInactiveStaff(int $memberId, ?string $nickname, string $role, string $lastSeenAt): void
    {
        $this->alerts[] = $memberId;
    }
}

/** Records which members had their content anonymized. */
final class SpyAnonymizer implements MemberContentAnonymizer
{
    /** @var list<array{int, string}> */
    public array $calls = [];

    public function anonymizeFor(int $memberId, string $email): void
    {
        $this->calls[] = [$memberId, $email];
    }
}

/** @return array{InMemoryMembers, SpyInactivityNotifier, SpyAnonymizer, Closure(string): array{noticed: int, anonymized: int, alerted: int}} */
function inactivityCycle(): array
{
    $members = new InMemoryMembers;
    $notifier = new SpyInactivityNotifier;
    $anonymizer = new SpyAnonymizer;
    $useCase = new AnonymizeInactiveMembers($members, $notifier, [$anonymizer]);
    $run = fn (string $now) => $useCase->execute(new DateTimeImmutable($now), 5, 30);

    return [$members, $notifier, $anonymizer, $run];
}

it('sends the notice 30 days before the 5 years, and only once', function () {
    [$members, $notifier, , $run] = inactivityCycle();
    $id = $members->add('ana@example.test', new DateTimeImmutable('2026-03-01 03:00'));

    $run('2031-01-29 03:15');
    expect($notifier->notices)->toBe([]);

    $run('2031-01-30 03:15');
    $run('2031-01-31 03:15');

    expect($notifier->notices)->toBe([['email' => 'ana@example.test', 'anonymizeOn' => '2031-03-01']])
        ->and($members->rows[$id]['anonymizedAt'])->toBeNull();
});

it('anonymizes at the term once the notice is 30 days old', function () {
    [$members, , $anonymizer, $run] = inactivityCycle();
    $id = $members->add('ana@example.test', new DateTimeImmutable('2026-03-01 03:00'));

    $run('2031-01-30 03:15');
    $run('2031-02-28 03:15');
    expect($members->rows[$id]['anonymizedAt'])->toBeNull();

    $result = $run('2031-03-01 03:15');

    expect($result['anonymized'])->toBe(1)
        ->and($members->rows[$id]['email'])->toBeNull()
        ->and($members->rows[$id]['providerId'])->toBeNull()
        ->and($members->rows[$id]['nickname'])->toBe("apelido{$id}")
        ->and($anonymizer->calls)->toBe([[$id, 'ana@example.test']]);
});

it('never anonymizes without a notice sent at least 30 days before', function () {
    [$members, $notifier, , $run] = inactivityCycle();
    // The routine was stopped for years: the account is long past the term but was never noticed.
    $id = $members->add('ana@example.test', new DateTimeImmutable('2020-01-01'));

    $first = $run('2031-03-01 03:15');
    $tooSoon = $run('2031-03-30 03:15');
    $due = $run('2031-03-31 03:15');

    expect($first)->toBe(['noticed' => 1, 'anonymized' => 0, 'alerted' => 0])
        ->and($tooSoon['anonymized'])->toBe(0)
        ->and($due['anonymized'])->toBe(1)
        ->and($notifier->notices[0]['anonymizeOn'])->toBe('2031-03-31')
        ->and($members->rows[$id]['anonymizedAt'])->not->toBeNull();
});

it('leaves an anonymized account alone on the next runs', function () {
    [$members, $notifier, $anonymizer, $run] = inactivityCycle();
    $members->add('ana@example.test', new DateTimeImmutable('2026-03-01'), noticeAt: new DateTimeImmutable('2031-01-30'));
    $run('2031-03-01 03:15');

    $again = $run('2031-03-02 03:15');

    expect($again)->toBe(['noticed' => 0, 'anonymized' => 0, 'alerted' => 0])
        ->and($anonymizer->calls)->toHaveCount(1)
        ->and($notifier->notices)->toBe([]);
});

it('only alerts the admins about a panel account, once per cycle', function () {
    [$members, $notifier, $anonymizer, $run] = inactivityCycle();
    $id = $members->add('mod@example.test', new DateTimeImmutable('2026-03-01'), role: 'moderator');

    $run('2031-01-30 03:15');
    $run('2031-03-02 03:15');
    $run('2031-03-03 03:15');

    expect($notifier->notices)->toHaveCount(1)
        ->and($notifier->alerts)->toBe([$id])
        ->and($anonymizer->calls)->toBe([])
        ->and($members->rows[$id]['anonymizedAt'])->toBeNull();
});

it('restarts the cycle when the member signs in after the notice', function () {
    [$members, $notifier, $anonymizer, $run] = inactivityCycle();
    $id = $members->add('ana@example.test', new DateTimeImmutable('2026-03-01'));
    $run('2031-01-30 03:15');

    (new SignInWithIdentity($members))->execute(new Identity("google-{$id}", 'Ana', 'ana@example.test', null));
    $run(date('Y-m-d H:i', strtotime('+31 days')));

    expect($anonymizer->calls)->toBe([])
        ->and($members->rows[$id]['noticeAt'])->toBeNull()
        ->and($notifier->notices)->toHaveCount(1);
});

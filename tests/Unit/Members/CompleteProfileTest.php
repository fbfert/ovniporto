<?php

use App\Application\Members\UseCases\CheckNickname;
use App\Application\Members\UseCases\CompleteProfile;
use App\Application\Privacy\UseCases\CurrentTermsVersion;
use App\Domain\Content\Contracts\ContentBlockRepository;
use App\Domain\Members\AgeNotDeclared;
use App\Domain\Members\Contracts\MemberRepository;
use App\Domain\Members\TermsNotAccepted;
use App\Domain\Privacy\ConsentType;
use Tests\Support\SpyConsentLedger;

function completeProfileWith(MemberRepository $members, SpyConsentLedger $ledger): CompleteProfile
{
    $noFinalTexts = new class implements ContentBlockRepository
    {
        public function values(array $keys): array
        {
            return array_fill_keys($keys, null);
        }
    };

    return new CompleteProfile($members, new CheckNickname($members), $ledger, new CurrentTermsVersion($noFinalTexts));
}

it('records the terms and the minimum age declaration together', function () {
    $members = Mockery::mock(MemberRepository::class);
    $members->shouldReceive('nicknameTaken')->andReturnFalse();
    $members->shouldReceive('completeProfile')->once()->with(7, 'vigia', 'Lages', Mockery::type(DateTimeInterface::class));
    $ledger = new SpyConsentLedger;

    completeProfileWith($members, $ledger)->execute(7, 'vigia', ' Lages ', termsAccepted: true, ageDeclared: true);

    expect(array_map(fn (array $r) => [$r['type'], $r['version']], $ledger->records))->toBe([
        [ConsentType::Terms, 'inicial'],
        [ConsentType::MinimumAge, '2026-10-16'],
    ]);
});

it('activates nothing without the age declaration', function () {
    $members = Mockery::mock(MemberRepository::class);
    $members->shouldNotReceive('completeProfile');
    $ledger = new SpyConsentLedger;

    expect(fn () => completeProfileWith($members, $ledger)->execute(7, 'vigia', null, termsAccepted: true, ageDeclared: false))
        ->toThrow(AgeNotDeclared::class)
        ->and($ledger->records)->toBe([]);
});

it('activates nothing without the terms', function () {
    $members = Mockery::mock(MemberRepository::class);
    $members->shouldNotReceive('completeProfile');
    $ledger = new SpyConsentLedger;

    expect(fn () => completeProfileWith($members, $ledger)->execute(7, 'vigia', null, termsAccepted: false, ageDeclared: true))
        ->toThrow(TermsNotAccepted::class)
        ->and($ledger->records)->toBe([]);
});

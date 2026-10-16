<?php

use App\Application\Privacy\UseCases\AcceptCurrentTerms;
use App\Application\Privacy\UseCases\CurrentTermsVersion;
use App\Domain\Content\Contracts\ContentBlockRepository;
use App\Domain\Members\AgeNotDeclared;
use App\Domain\Members\Contracts\MemberRepository;
use App\Domain\Members\TermsNotAccepted;
use App\Domain\Privacy\ConsentType;
use Tests\Support\SpyConsentLedger;

/** @param array<string, string|null> $blocks */
function termsBlocks(array $blocks): ContentBlockRepository
{
    return new class($blocks) implements ContentBlockRepository
    {
        public function __construct(private array $blocks) {}

        public function values(array $keys): array
        {
            return array_combine($keys, array_map(fn (string $key) => $this->blocks[$key] ?? null, $keys));
        }
    };
}

it('is "inicial" while neither text is marked final, even with a saved date', function () {
    $version = new CurrentTermsVersion(termsBlocks(['terms_updated_at' => '2026-10-04', 'privacy_updated_at' => '2026-10-04']));

    expect($version->execute())->toBe('inicial');
});

it('is the newest date among the final texts', function () {
    $version = new CurrentTermsVersion(termsBlocks([
        'terms_final' => '1', 'terms_updated_at' => '2026-11-15',
        'privacy_final' => '', 'privacy_updated_at' => '2026-12-20',
    ]));

    expect($version->execute())->toBe('2026-11-15');
});

function pendingAcceptances(SpyConsentLedger $ledger, MemberRepository $members): AcceptCurrentTerms
{
    return new AcceptCurrentTerms($ledger, new CurrentTermsVersion(termsBlocks(['terms_final' => '1', 'terms_updated_at' => '2026-11-15'])), $members);
}

it('has nothing pending when the current terms and the age are on record', function () {
    $ledger = new SpyConsentLedger;
    $members = Mockery::mock(MemberRepository::class);
    $members->shouldNotReceive('acceptTerms');
    $ledger->record(ConsentType::Terms, '2026-11-15', 7);
    $ledger->record(ConsentType::MinimumAge, '2026-10-16', 7);
    $terms = pendingAcceptances($ledger, $members);

    $terms->execute(7, termsAccepted: false, ageDeclared: false);

    expect($terms->pending(7))->toBe([])->and($ledger->records)->toHaveCount(2);
});

it('asks again only for the terms when the accepted version is not the current one', function () {
    $ledger = new SpyConsentLedger;
    $members = Mockery::mock(MemberRepository::class);
    $members->shouldReceive('acceptTerms')->once()->with(7, Mockery::type(DateTimeInterface::class));
    $ledger->record(ConsentType::Terms, 'inicial', 7);
    $ledger->record(ConsentType::MinimumAge, '2026-10-16', 7);
    $terms = pendingAcceptances($ledger, $members);
    expect($terms->pending(7))->toBe(['terms']);

    $terms->execute(7, termsAccepted: true, ageDeclared: false);

    expect($terms->pending(7))->toBe([])
        ->and(end($ledger->records)['version'])->toBe('2026-11-15')
        ->and($ledger->records)->toHaveCount(3);
});

it('asks only for the age from a member who signed up before the declaration existed', function () {
    $ledger = new SpyConsentLedger;
    $members = Mockery::mock(MemberRepository::class);
    $members->shouldNotReceive('acceptTerms');
    $ledger->record(ConsentType::Terms, '2026-11-15', 7);
    $terms = pendingAcceptances($ledger, $members);
    expect($terms->pending(7))->toBe(['age']);

    expect(fn () => $terms->execute(7, termsAccepted: true, ageDeclared: false))->toThrow(AgeNotDeclared::class);
    $terms->execute(7, termsAccepted: false, ageDeclared: true);

    expect($terms->pending(7))->toBe([])
        ->and(end($ledger->records)['type'])->toBe(ConsentType::MinimumAge);
});

it('asks for both on one screen and records both', function () {
    $ledger = new SpyConsentLedger;
    $members = Mockery::mock(MemberRepository::class);
    $members->shouldReceive('acceptTerms')->once();
    $ledger->record(ConsentType::Terms, 'inicial', 7);
    $terms = pendingAcceptances($ledger, $members);
    expect($terms->pending(7))->toBe(['terms', 'age']);

    expect(fn () => $terms->execute(7, termsAccepted: false, ageDeclared: true))->toThrow(TermsNotAccepted::class);
    $terms->execute(7, termsAccepted: true, ageDeclared: true);

    expect($terms->pending(7))->toBe([])
        ->and(array_map(fn (array $r) => $r['type'], array_slice($ledger->records, 1)))->toBe([ConsentType::Terms, ConsentType::MinimumAge]);
});

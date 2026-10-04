<?php

use App\Application\Privacy\UseCases\AcceptCurrentTerms;
use App\Application\Privacy\UseCases\CurrentTermsVersion;
use App\Domain\Content\Contracts\ContentBlockRepository;
use App\Domain\Members\Contracts\MemberRepository;
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

it('asks again only when the accepted version is not the current one', function () {
    $ledger = new SpyConsentLedger;
    $members = Mockery::mock(MemberRepository::class);
    $members->shouldReceive('acceptTerms')->once()->with(7, Mockery::type(DateTimeInterface::class));
    $terms = new AcceptCurrentTerms($ledger, new CurrentTermsVersion(termsBlocks(['terms_final' => '1', 'terms_updated_at' => '2026-11-15'])), $members);

    $ledger->record(ConsentType::Terms, 'inicial', 7);
    expect($terms->pending(7))->toBeTrue();

    $terms->execute(7);

    expect($terms->pending(7))->toBeFalse()
        ->and(end($ledger->records)['version'])->toBe('2026-11-15');
});

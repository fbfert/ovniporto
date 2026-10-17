<?php

namespace App\Application\Privacy\UseCases;

use App\Domain\Members\AgeNotDeclared;
use App\Domain\Members\Contracts\MemberRepository;
use App\Domain\Members\TermsNotAccepted;
use App\Domain\Privacy\ConsentType;
use App\Domain\Privacy\Contracts\ConsentLedger;
use DateTimeImmutable;

/**
 * What a member still has to agree to before going on: the current terms (they changed
 * since the last yes) and the minimum age declaration (accounts from before it existed).
 * Both fit on one screen; only what is pending is asked and recorded.
 */
final readonly class AcceptCurrentTerms
{
    public const TERMS = 'terms';

    public const AGE = 'age';

    public function __construct(
        private ConsentLedger $ledger,
        private CurrentTermsVersion $currentVersion,
        private MemberRepository $members,
    ) {}

    /** @return list<self::TERMS|self::AGE> */
    public function pending(int $memberId): array
    {
        $pending = [];
        if ($this->ledger->latestVersion($memberId, ConsentType::Terms) !== $this->currentVersion->execute()) {
            $pending[] = self::TERMS;
        }
        if ($this->ledger->latestVersion($memberId, ConsentType::MinimumAge) !== ConsentType::MinimumAge->textVersion()) {
            $pending[] = self::AGE;
        }

        return $pending;
    }

    /**
     * What is pending and was left unchecked, all at once, so the screen can flag every box.
     *
     * @return list<self::TERMS|self::AGE>
     */
    public function missing(int $memberId, bool $termsAccepted, bool $ageDeclared): array
    {
        $answers = [self::TERMS => $termsAccepted, self::AGE => $ageDeclared];

        return array_values(array_filter($this->pending($memberId), fn (string $item) => ! $answers[$item]));
    }

    public function execute(int $memberId, bool $termsAccepted, bool $ageDeclared): void
    {
        $pending = $this->pending($memberId);
        $acceptsTerms = in_array(self::TERMS, $pending, true);
        $declaresAge = in_array(self::AGE, $pending, true);
        if ($acceptsTerms && ! $termsAccepted) {
            throw new TermsNotAccepted;
        }
        if ($declaresAge && ! $ageDeclared) {
            throw new AgeNotDeclared;
        }

        $now = new DateTimeImmutable;
        if ($acceptsTerms) {
            $this->members->acceptTerms($memberId, $now);
            $this->ledger->record(ConsentType::Terms, $this->currentVersion->execute(), $memberId, givenAt: $now);
        }
        if ($declaresAge) {
            $this->ledger->record(ConsentType::MinimumAge, ConsentType::MinimumAge->textVersion(), $memberId, givenAt: $now);
        }
    }
}

<?php

namespace App\Application\Privacy\UseCases;

use App\Domain\Members\Contracts\MemberRepository;
use App\Domain\Privacy\ConsentType;
use App\Domain\Privacy\Contracts\ConsentLedger;
use DateTimeImmutable;

/** When the terms change, members agree again before going on. */
final readonly class AcceptCurrentTerms
{
    public function __construct(
        private ConsentLedger $ledger,
        private CurrentTermsVersion $currentVersion,
        private MemberRepository $members,
    ) {}

    public function pending(int $memberId): bool
    {
        return $this->ledger->latestVersion($memberId, ConsentType::Terms) !== $this->currentVersion->execute();
    }

    public function execute(int $memberId): void
    {
        $now = new DateTimeImmutable;
        $this->members->acceptTerms($memberId, $now);
        $this->ledger->record(ConsentType::Terms, $this->currentVersion->execute(), $memberId, givenAt: $now);
    }
}

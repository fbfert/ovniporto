<?php

namespace App\Application\Members\UseCases;

use App\Application\Privacy\UseCases\CurrentTermsVersion;
use App\Domain\Members\AgeNotDeclared;
use App\Domain\Members\Contracts\MemberRepository;
use App\Domain\Members\NicknameUnavailable;
use App\Domain\Members\TermsNotAccepted;
use App\Domain\Privacy\ConsentType;
use App\Domain\Privacy\Contracts\ConsentLedger;
use DateTimeImmutable;

/**
 * First visit after Google: public nickname, optional city, the terms and the minimum age
 * declaration. Only then is the account active. No birth date is ever asked.
 */
final readonly class CompleteProfile
{
    public function __construct(
        private MemberRepository $members,
        private CheckNickname $checkNickname,
        private ConsentLedger $consents,
        private CurrentTermsVersion $termsVersion,
    ) {}

    public function execute(int $memberId, string $nickname, ?string $city, bool $termsAccepted, bool $ageDeclared): void
    {
        if (! $termsAccepted) {
            throw new TermsNotAccepted;
        }
        if (! $ageDeclared) {
            throw new AgeNotDeclared;
        }
        $problem = $this->checkNickname->execute($nickname, $memberId);
        if ($problem !== null) {
            throw new NicknameUnavailable($problem);
        }

        $now = new DateTimeImmutable;
        $this->members->completeProfile($memberId, $nickname, self::city($city), $now);
        $this->consents->record(ConsentType::Terms, $this->termsVersion->execute(), $memberId, givenAt: $now);
        $this->consents->record(ConsentType::MinimumAge, ConsentType::MinimumAge->textVersion(), $memberId, givenAt: $now);
    }

    public static function city(?string $city): ?string
    {
        return $city === null || trim($city) === '' ? null : trim($city);
    }
}

<?php

namespace App\Application\Members\UseCases;

use App\Domain\Members\Contracts\MemberRepository;
use App\Domain\Members\NicknameUnavailable;
use App\Domain\Members\TermsNotAccepted;
use DateTimeImmutable;

/** First visit after Google: public nickname, optional city and the terms. Only then is the account active. */
final readonly class CompleteProfile
{
    public function __construct(
        private MemberRepository $members,
        private CheckNickname $checkNickname,
    ) {}

    public function execute(int $memberId, string $nickname, ?string $city, bool $termsAccepted): void
    {
        if (! $termsAccepted) {
            throw new TermsNotAccepted;
        }
        $problem = $this->checkNickname->execute($nickname, $memberId);
        if ($problem !== null) {
            throw new NicknameUnavailable($problem);
        }

        $this->members->completeProfile($memberId, $nickname, self::city($city), new DateTimeImmutable);
    }

    public static function city(?string $city): ?string
    {
        return $city === null || trim($city) === '' ? null : trim($city);
    }
}

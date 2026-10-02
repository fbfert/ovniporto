<?php

namespace App\Application\Members\UseCases;

use App\Domain\Members\Contracts\MemberRepository;
use App\Domain\Members\NicknameUnavailable;

/** "Meus dados": nickname and city. Name and e-mail come from Google and are read-only. */
final readonly class UpdateProfile
{
    public function __construct(
        private MemberRepository $members,
        private CheckNickname $checkNickname,
    ) {}

    public function execute(int $memberId, string $nickname, ?string $city): void
    {
        $problem = $this->checkNickname->execute($nickname, $memberId);
        if ($problem !== null) {
            throw new NicknameUnavailable($problem);
        }

        $this->members->updateProfile($memberId, $nickname, CompleteProfile::city($city));
    }
}

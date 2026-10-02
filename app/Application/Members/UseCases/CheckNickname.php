<?php

namespace App\Application\Members\UseCases;

use App\Domain\Members\Contracts\MemberRepository;
use App\Domain\Members\Nickname;

final readonly class CheckNickname
{
    public function __construct(private MemberRepository $members) {}

    /** @return 'format'|'reserved'|'taken'|null why the nickname can't be used, or null when it is free */
    public function execute(string $nickname, ?int $exceptMemberId = null): ?string
    {
        return Nickname::problem($nickname)
            ?? ($this->members->nicknameTaken($nickname, $exceptMemberId) ? 'taken' : null);
    }

    public function suggestFor(string $fullName): string
    {
        return Nickname::suggest($fullName, fn (string $candidate) => $this->members->nicknameTaken($candidate));
    }
}

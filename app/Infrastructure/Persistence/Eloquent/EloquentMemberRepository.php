<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Members\Contracts\MemberRepository;
use App\Models\Member;

final class EloquentMemberRepository implements MemberRepository
{
    public function countActive(): int
    {
        return Member::query()->whereNotNull('terms_accepted_at')->count();
    }
}

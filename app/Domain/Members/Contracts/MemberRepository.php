<?php

namespace App\Domain\Members\Contracts;

interface MemberRepository
{
    public function countActive(): int;
}

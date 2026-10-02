<?php

namespace App\Policies;

use App\Models\Member;
use App\Models\Sighting;

/** A member only touches their own reports. */
class SightingPolicy
{
    public function update(Member $member, Sighting $sighting): bool
    {
        return $sighting->member_id === $member->id;
    }

    public function delete(Member $member, Sighting $sighting): bool
    {
        return $sighting->member_id === $member->id;
    }
}

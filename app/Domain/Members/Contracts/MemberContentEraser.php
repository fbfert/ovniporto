<?php

namespace App\Domain\Members\Contracts;

/**
 * Port other modules implement so deleting an account stays decoupled from them:
 * Sightings deletes reports and photos, Orders anonymizes what tax law keeps.
 */
interface MemberContentEraser
{
    public function eraseFor(int $memberId): void;
}

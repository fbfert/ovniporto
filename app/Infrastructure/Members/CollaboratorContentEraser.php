<?php

namespace App\Infrastructure\Members;

use App\Domain\Members\Contracts\MemberContentAnonymizer;
use App\Domain\Members\Contracts\MemberContentEraser;
use App\Models\ResearchCollaborator;

/** Account deletion, Origin side: offers to help the research sent with the account e-mail go too. */
final class CollaboratorContentEraser implements MemberContentAnonymizer, MemberContentEraser
{
    public function eraseFor(int $memberId, string $email): void
    {
        ResearchCollaborator::query()->where('email', mb_strtolower($email))->delete();
    }

    /** Anonymized after inactivity: the same as on deletion. */
    public function anonymizeFor(int $memberId, string $email): void
    {
        $this->eraseFor($memberId, $email);
    }
}

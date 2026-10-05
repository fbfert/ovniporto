<?php

namespace App\Infrastructure\Members;

use App\Domain\Members\Contracts\MemberContentEraser;
use App\Models\ResearchCollaborator;

/** Account deletion, Origin side: offers to help the research sent with the account e-mail go too. */
final class CollaboratorContentEraser implements MemberContentEraser
{
    public function eraseFor(int $memberId, string $email): void
    {
        ResearchCollaborator::query()->where('email', mb_strtolower($email))->delete();
    }
}

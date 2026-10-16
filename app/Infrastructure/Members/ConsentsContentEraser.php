<?php

namespace App\Infrastructure\Members;

use App\Domain\Members\Contracts\MemberContentAnonymizer;
use App\Domain\Members\Contracts\MemberContentEraser;
use App\Domain\Privacy\Contracts\ConsentLedger;

/** Account deletion, Privacy side: consents keep type, version and date, nothing that points to the person. */
final readonly class ConsentsContentEraser implements MemberContentAnonymizer, MemberContentEraser
{
    public function __construct(private ConsentLedger $consents) {}

    public function eraseFor(int $memberId, string $email): void
    {
        $this->consents->anonymize($memberId, $email);
    }

    /** Anonymized after inactivity: the same as on deletion. */
    public function anonymizeFor(int $memberId, string $email): void
    {
        $this->eraseFor($memberId, $email);
    }
}

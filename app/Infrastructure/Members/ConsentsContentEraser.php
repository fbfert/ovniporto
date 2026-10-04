<?php

namespace App\Infrastructure\Members;

use App\Domain\Members\Contracts\MemberContentEraser;
use App\Domain\Privacy\Contracts\ConsentLedger;

/** Account deletion, Privacy side: consents keep type, version and date, nothing that points to the person. */
final readonly class ConsentsContentEraser implements MemberContentEraser
{
    public function __construct(private ConsentLedger $consents) {}

    public function eraseFor(int $memberId, string $email): void
    {
        $this->consents->anonymize($memberId, $email);
    }
}

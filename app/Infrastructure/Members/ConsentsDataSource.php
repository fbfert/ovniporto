<?php

namespace App\Infrastructure\Members;

use App\Domain\Members\Contracts\MemberDataSource;
use App\Domain\Privacy\Contracts\ConsentLedger;

/** "Consentimentos" of "Baixar meus dados": every yes, with type, version and date. */
final readonly class ConsentsDataSource implements MemberDataSource
{
    public function __construct(private ConsentLedger $consents) {}

    public function section(): string
    {
        return 'consentimentos';
    }

    public function exportFor(int $memberId, string $email): array
    {
        return $this->consents->historyOf($memberId, $email);
    }
}

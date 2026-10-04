<?php

namespace Tests\Support;

use App\Domain\Privacy\ConsentType;
use App\Domain\Privacy\Contracts\ConsentLedger;
use DateTimeImmutable;

/** In-memory ledger for unit tests of the use cases that record consents. */
final class SpyConsentLedger implements ConsentLedger
{
    /** @var list<array{type: ConsentType, version: string, memberId: ?int, email: ?string, subject: ?string}> */
    public array $records = [];

    public function record(
        ConsentType $type,
        string $version,
        ?int $memberId = null,
        ?string $email = null,
        ?string $subject = null,
        ?DateTimeImmutable $givenAt = null,
    ): void {
        $this->records[] = compact('type', 'version', 'memberId', 'email', 'subject');
    }

    public function latestVersion(int $memberId, ConsentType $type): ?string
    {
        $versions = array_column(array_filter($this->records, fn (array $r) => $r['memberId'] === $memberId && $r['type'] === $type), 'version');

        return $versions === [] ? null : end($versions);
    }

    public function historyOf(int $memberId, string $email): array
    {
        return [];
    }

    public function anonymize(int $memberId, string $email): void {}
}

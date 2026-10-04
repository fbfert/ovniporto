<?php

namespace App\Domain\Privacy\Contracts;

use App\Domain\Privacy\ConsentType;
use DateTimeImmutable;

/**
 * Every consent with type, version, date, IP and browser. The implementation
 * reads IP and browser from the request that carried the consent.
 */
interface ConsentLedger
{
    /** @param string|null $subject what the consent is about, e.g. "relato:12" */
    public function record(
        ConsentType $type,
        string $version,
        ?int $memberId = null,
        ?string $email = null,
        ?string $subject = null,
        ?DateTimeImmutable $givenAt = null,
    ): void;

    public function latestVersion(int $memberId, ConsentType $type): ?string;

    /** @return list<array{tipo: string, versao: string, assunto: ?string, dado_em: string}> */
    public function historyOf(int $memberId, string $email): array;

    /** Keeps type, version and date; drops IP, browser, e-mail, subject and the link to the person. */
    public function anonymize(int $memberId, string $email): void;
}

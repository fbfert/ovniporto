<?php

namespace App\Domain\Privacy\Contracts;

/**
 * "O que fazemos na prática": the privacy guarantees in plain words, built from
 * the same constants the code enforces, so the page can't drift from the system.
 */
interface PrivacyPractices
{
    public function title(): string;

    /** @return list<string> */
    public function all(): array;
}

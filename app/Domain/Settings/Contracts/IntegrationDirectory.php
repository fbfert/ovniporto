<?php

namespace App\Domain\Settings\Contracts;

/**
 * The integrations that stay in the .env, described without a single key:
 * whether each one is set, in which mode, and whether it needs attention.
 */
interface IntegrationDirectory
{
    /** @return list<array{name: string, configured: bool, mode: ?string, attention: bool}> */
    public function describe(): array;
}

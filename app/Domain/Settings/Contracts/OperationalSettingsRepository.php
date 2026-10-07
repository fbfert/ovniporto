<?php

namespace App\Domain\Settings\Contracts;

use App\Domain\Settings\OperationalSetting;

/**
 * What the admin saved in /painel/coordenadas. Only saved keys are present:
 * a missing key means the .env value is in force.
 */
interface OperationalSettingsRepository
{
    /** @return array<string, string> setting value => plain value (secrets decrypted, in memory only) */
    public function values(): array;

    /**
     * Saves and forgets in one go: a null value removes the key (back to the .env).
     *
     * @param  array<string, string|null>  $changes  setting value => new value
     */
    public function save(array $changes, ?int $actorId): void;

    /** Changes on every save, so long-running workers know when to reload. */
    public function version(): string;

    public function has(OperationalSetting $setting): bool;

    /** Whether the last SMTP test went through, so the panel home can point at a broken mailer. */
    public function recordMailTest(bool $sent): void;

    public function lastMailTest(): ?bool;
}

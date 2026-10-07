<?php

namespace Tests\Support;

use App\Domain\Settings\Contracts\OperationalSettingsRepository;
use App\Domain\Settings\OperationalSetting;

final class InMemoryOperationalSettings implements OperationalSettingsRepository
{
    public int $saves = 0;

    public ?bool $lastTest = null;

    /** @param array<string, string> $values */
    public function __construct(public array $values = []) {}

    public function values(): array
    {
        return $this->values;
    }

    public function save(array $changes, ?int $actorId): void
    {
        foreach ($changes as $key => $value) {
            if ($value === null || $value === '') {
                unset($this->values[$key]);
            } else {
                $this->values[$key] = $value;
            }
        }
        $this->saves++;
    }

    public function version(): string
    {
        return 'v'.$this->saves;
    }

    public function has(OperationalSetting $setting): bool
    {
        return array_key_exists($setting->value, $this->values);
    }

    public function recordMailTest(bool $sent): void
    {
        $this->lastTest = $sent;
    }

    public function lastMailTest(): ?bool
    {
        return $this->lastTest;
    }
}

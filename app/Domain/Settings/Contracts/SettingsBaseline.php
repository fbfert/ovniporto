<?php

namespace App\Domain\Settings\Contracts;

use App\Domain\Settings\OperationalSetting;

/** What the .env says about a setting, before the panel's value is laid over it. */
interface SettingsBaseline
{
    public function envValue(OperationalSetting $setting): mixed;
}

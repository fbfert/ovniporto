<?php

namespace App\Domain\Settings;

/** The blocks of /painel/coordenadas; each one is saved on its own. */
enum SettingsGroup: string
{
    case Mail = 'correio';
    case Alerts = 'alertas';
    case Shipping = 'frete';

    /** @return list<OperationalSetting> */
    public function settings(): array
    {
        return array_values(array_filter(OperationalSetting::cases(), fn (OperationalSetting $s) => $s->group() === $this));
    }
}

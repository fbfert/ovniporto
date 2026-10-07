<?php

namespace App\Application\Settings\UseCases;

use App\Domain\Settings\Contracts\IntegrationDirectory;
use App\Domain\Settings\Contracts\OperationalSettingsRepository;
use App\Domain\Settings\Contracts\SettingsBaseline;
use App\Domain\Settings\OperationalSetting;
use App\Domain\Settings\SettingsGroup;

/**
 * /painel/coordenadas: every setting with the value in force and where it
 * comes from. A secret goes out only as "set" or "not set".
 */
final readonly class ShowCoordinates
{
    public function __construct(
        private OperationalSettingsRepository $settings,
        private SettingsBaseline $baseline,
        private IntegrationDirectory $integrations,
    ) {}

    /** @return array<string, mixed> */
    public function execute(): array
    {
        $saved = $this->settings->values();
        $groups = [];
        foreach (SettingsGroup::cases() as $group) {
            foreach ($group->settings() as $setting) {
                $groups[$group->value][$setting->field()] = $this->field($setting, $saved);
            }
        }

        return [
            'groups' => $groups,
            'integrations' => $this->integrations->describe(),
            ...$this->health(),
        ];
    }

    /**
     * What the panel home warns about: no address for the alerts, or a mailer that failed its last test.
     *
     * @return array{alertsMissing: bool, mailBroken: bool}
     */
    public function health(): array
    {
        $alerts = $this->settings->values()[OperationalSetting::AlertsEmail->value] ?? $this->baseline->envValue(OperationalSetting::AlertsEmail);

        return [
            'alertsMissing' => blank($alerts),
            'mailBroken' => $this->settings->lastMailTest() === false,
        ];
    }

    /**
     * @param  array<string, string>  $saved
     * @return array{value: ?string, env: ?string, fromPanel: bool, set?: bool}
     */
    private function field(OperationalSetting $setting, array $saved): array
    {
        $fromPanel = array_key_exists($setting->value, $saved);
        if ($setting->isSecret()) {
            return [
                'value' => null,
                'env' => null,
                'fromPanel' => $fromPanel,
                'set' => $fromPanel || filled($this->baseline->envValue($setting)),
            ];
        }
        $env = $this->baseline->envValue($setting);

        return [
            'value' => $saved[$setting->value] ?? null,
            'env' => blank($env) ? null : (string) $env,
            'fromPanel' => $fromPanel,
        ];
    }
}

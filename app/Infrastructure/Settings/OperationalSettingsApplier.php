<?php

namespace App\Infrastructure\Settings;

use App\Domain\Settings\Contracts\OperationalSettingsRepository;
use App\Domain\Settings\Contracts\SettingsBaseline;
use App\Domain\Settings\OperationalSetting;
use App\Domain\Shipping\Contracts\ShippingProvider;
use Illuminate\Container\Container;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Mail\MailManager;
use Laravel\Horizon\Horizon;
use Throwable;

/**
 * Lays the panel's coordinates over config() at run time, never at boot: a
 * boot-time override would be frozen (password included) by `config:cache`.
 *
 * Web requests call refresh() from a middleware; workers before every job and
 * the Horizon master on every loop, so a save is in force without a restart.
 * The .env values are kept aside the first time, to go back to when a field
 * is emptied.
 */
final class OperationalSettingsApplier implements SettingsBaseline
{
    /** @var array<string, mixed>|null config key => value from the .env */
    private ?array $baseline = null;

    private ?string $applied = null;

    public function __construct(
        private readonly OperationalSettingsRepository $settings,
        private readonly Config $config,
        private readonly Container $container,
    ) {}

    /** Re-applies only when something was saved since the last time. A failure keeps the .env in force. */
    public function refresh(): void
    {
        try {
            $version = $this->settings->version();
            if ($version !== $this->applied) {
                $this->apply($this->settings->values(), reapplying: $this->applied !== null);
                $this->applied = $version;
            }
        } catch (Throwable $e) {
            report($e);
        }
    }

    /** The value the .env gives this setting, before any override. */
    public function envValue(OperationalSetting $setting): mixed
    {
        return $this->baseline()[$setting->configKey()] ?? null;
    }

    /**
     * @param  array<string, string>  $values
     * @param  bool  $reapplying  only then can something have been built from older values
     */
    private function apply(array $values, bool $reapplying): void
    {
        $baseline = $this->baseline();
        foreach (OperationalSetting::cases() as $setting) {
            $value = $values[$setting->value] ?? null;
            $this->config->set($setting->configKey(), $value === null ? $baseline[$setting->configKey()] : self::typed($setting, $value));
        }

        // A server saved in the panel means "send by SMTP", even when the .env still says "log".
        $smtpFromPanel = isset($values[OperationalSetting::MailHost->value]) && $baseline['mail.default'] === 'log';
        $this->config->set('mail.default', $smtpFromPanel ? 'smtp' : $baseline['mail.default']);

        $alerts = $this->config->get('ovniporto.alerts_email');
        Horizon::$email = filled($alerts) ? (string) $alerts : null;

        // Long-running processes keep the SMTP transport and the label sender once built.
        if ($reapplying) {
            if ($this->container->resolved('mail.manager')) {
                $this->container->make(MailManager::class)->purge('smtp');
            }
            $this->container->forgetInstance(ShippingProvider::class);
        }
    }

    /** @return array<string, mixed> */
    private function baseline(): array
    {
        if ($this->baseline === null) {
            $this->baseline = ['mail.default' => $this->config->get('mail.default')];
            foreach (OperationalSetting::cases() as $setting) {
                $this->baseline[$setting->configKey()] = $this->config->get($setting->configKey());
            }
        }

        return $this->baseline;
    }

    private static function typed(OperationalSetting $setting, string $value): string|int
    {
        return in_array($setting, [OperationalSetting::MailPort, OperationalSetting::PackageLength, OperationalSetting::PackageWidth, OperationalSetting::PackageHeight], true)
            ? (int) $value
            : $value;
    }
}

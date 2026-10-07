<?php

namespace App\Domain\Settings;

/**
 * The closed list of settings the admin may change in /painel/coordenadas.
 * Each one overrides exactly one config() key: nothing outside this list can
 * be written from the panel, so a tampered form never reaches app.key or the
 * database. Empty means "use the .env value".
 */
enum OperationalSetting: string
{
    case MailHost = 'mail.host';
    case MailPort = 'mail.port';
    case MailScheme = 'mail.scheme';
    case MailUsername = 'mail.username';
    case MailPassword = 'mail.password';
    case MailFromAddress = 'mail.from_address';
    case MailFromName = 'mail.from_name';

    case AlertsEmail = 'alerts.email';

    case SenderName = 'shipping.sender.name';
    case SenderPhone = 'shipping.sender.phone';
    case SenderEmail = 'shipping.sender.email';
    case SenderDocument = 'shipping.sender.document';
    case SenderPostalCode = 'shipping.sender.postal_code';
    case SenderStreet = 'shipping.sender.street';
    case SenderNumber = 'shipping.sender.number';
    case SenderDistrict = 'shipping.sender.district';
    case SenderCity = 'shipping.sender.city';
    case SenderState = 'shipping.sender.state';
    case PackageLength = 'shipping.package.length';
    case PackageWidth = 'shipping.package.width';
    case PackageHeight = 'shipping.package.height';

    public function group(): SettingsGroup
    {
        return match (true) {
            str_starts_with($this->value, 'mail.') => SettingsGroup::Mail,
            str_starts_with($this->value, 'alerts.') => SettingsGroup::Alerts,
            default => SettingsGroup::Shipping,
        };
    }

    /** The config() key this setting overrides. */
    public function configKey(): string
    {
        return match ($this) {
            self::MailHost, self::MailPort, self::MailScheme, self::MailUsername, self::MailPassword => 'mail.mailers.smtp.'.substr($this->value, 5),
            self::MailFromAddress => 'mail.from.address',
            self::MailFromName => 'mail.from.name',
            self::AlertsEmail => 'ovniporto.alerts_email',
            self::SenderPostalCode => 'services.melhor_envio.from_postal_code',
            default => 'ovniporto.'.$this->value,
        };
    }

    /** Stored encrypted, write-only, never sent back to the browser, the audit or the logs. */
    public function isSecret(): bool
    {
        return $this === self::MailPassword;
    }

    /** The key used by the panel form: "mail.from_address" → "fromAddress". */
    public function field(): string
    {
        $last = substr($this->value, strrpos($this->value, '.') + 1);
        $field = lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $last))));

        return $this->group() === SettingsGroup::Shipping && str_starts_with($this->value, 'shipping.package.')
            ? 'package'.ucfirst($field)
            : $field;
    }

    public static function forField(SettingsGroup $group, string $field): ?self
    {
        foreach ($group->settings() as $setting) {
            if ($setting->field() === $field) {
                return $setting;
            }
        }

        return null;
    }
}

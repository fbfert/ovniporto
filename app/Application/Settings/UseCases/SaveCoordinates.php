<?php

namespace App\Application\Settings\UseCases;

use App\Domain\Audit\Contracts\Auditor;
use App\Domain\Audit\Data\AuditEntry;
use App\Domain\Settings\Contracts\OperationalSettingsRepository;
use App\Domain\Settings\OperationalSetting;
use App\Domain\Settings\SettingsGroup;

/**
 * Saves one block of /painel/coordenadas. An empty field goes back to the
 * .env; an empty password field keeps the password (removing it is its own
 * action). The audit keeps before and after, with the password only as
 * "alterada" or "removida".
 */
final readonly class SaveCoordinates
{
    public const SUBJECT = 'coordinates';

    public function __construct(
        private OperationalSettingsRepository $settings,
        private Auditor $auditor,
    ) {}

    /** @param array<string, string|null> $fields form field => value */
    public function execute(int $actorId, SettingsGroup $group, array $fields): void
    {
        $changes = [];
        $passwordChanged = false;
        foreach ($group->settings() as $setting) {
            if (! array_key_exists($setting->field(), $fields)) {
                continue;
            }
            $value = self::normalize($setting, $fields[$setting->field()]);
            if ($setting->isSecret()) {
                if ($value === null) {
                    continue;
                }
                $passwordChanged = true;
            }
            $changes[$setting->value] = $value;
        }

        $this->auditor->audited(
            new AuditEntry($actorId, 'coordinates.updated', self::SUBJECT, self::subjectId($group), array_filter([
                'group' => $group->value,
                'password' => $passwordChanged ? 'alterada' : null,
            ])),
            fn () => $this->snapshot($group),
            fn () => $this->settings->save($changes, $actorId),
        );
    }

    public function removePassword(int $actorId): void
    {
        $this->auditor->audited(
            new AuditEntry($actorId, 'coordinates.password_removed', self::SUBJECT, self::subjectId(SettingsGroup::Mail), ['password' => 'removida']),
            fn () => $this->snapshot(SettingsGroup::Mail),
            fn () => $this->settings->save([OperationalSetting::MailPassword->value => null], $actorId),
        );
    }

    public static function subjectId(SettingsGroup $group): int
    {
        return (int) array_search($group, SettingsGroup::cases(), true) + 1;
    }

    /** @return array<string, string|null> what the audit may show: the secret only as set or not */
    private function snapshot(SettingsGroup $group): array
    {
        $saved = $this->settings->values();
        $snapshot = [];
        foreach ($group->settings() as $setting) {
            $snapshot[$setting->field()] = $setting->isSecret()
                ? (array_key_exists($setting->value, $saved) ? 'definida' : 'não definida')
                : ($saved[$setting->value] ?? null);
        }

        return $snapshot;
    }

    private static function normalize(OperationalSetting $setting, ?string $value): ?string
    {
        $value = $setting->isSecret() ? $value : trim((string) $value);
        if ($value === null || $value === '') {
            return null;
        }

        return match ($setting) {
            OperationalSetting::SenderDocument, OperationalSetting::SenderPostalCode, OperationalSetting::SenderPhone => (string) preg_replace('/\D/', '', $value),
            OperationalSetting::SenderState => strtoupper($value),
            OperationalSetting::MailFromAddress, OperationalSetting::AlertsEmail, OperationalSetting::SenderEmail => strtolower($value),
            default => $value,
        };
    }
}

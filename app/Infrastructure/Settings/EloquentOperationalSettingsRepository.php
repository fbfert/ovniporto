<?php

namespace App\Infrastructure\Settings;

use App\Domain\Settings\Contracts\OperationalSettingsRepository;
use App\Domain\Settings\OperationalSetting;
use App\Models\OperationalSettingRow;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Rows in operational_settings, mirrored in the cache exactly as stored: the
 * SMTP password stays encrypted there too and is only decrypted in memory.
 */
final readonly class EloquentOperationalSettingsRepository implements OperationalSettingsRepository
{
    private const ROWS = 'ops-settings:rows';

    private const VERSION = 'ops-settings:version';

    private const LAST_TEST = 'ops-settings:last-mail-test';

    public function __construct(private Cache $cache, private Encrypter $encrypter) {}

    public function values(): array
    {
        $values = [];
        foreach ($this->rows() as $key => [$value, $secret]) {
            if (OperationalSetting::tryFrom($key) === null) {
                continue;
            }
            if (! $secret) {
                $values[$key] = $value;

                continue;
            }
            try {
                $values[$key] = $this->encrypter->decryptString($value);
            } catch (DecryptException) {
                // Encrypted with another APP_KEY (a restore elsewhere): the .env value stays in force.
                report(new RuntimeException("Coordenada {$key} cifrada com outra APP_KEY: salve de novo no painel."));
            }
        }

        return $values;
    }

    public function save(array $changes, ?int $actorId): void
    {
        DB::transaction(function () use ($changes, $actorId) {
            foreach ($changes as $key => $value) {
                $setting = OperationalSetting::from($key);
                if ($value === null || $value === '') {
                    OperationalSettingRow::query()->where('key', $key)->delete();

                    continue;
                }
                OperationalSettingRow::query()->updateOrCreate(['key' => $key], [
                    'value' => $setting->isSecret() ? $this->encrypter->encryptString($value) : $value,
                    'is_secret' => $setting->isSecret(),
                    'updated_by' => $actorId,
                ]);
            }
        });

        $this->cache->forget(self::ROWS);
        $this->cache->forever(self::VERSION, (string) Str::uuid());
    }

    public function version(): string
    {
        $version = $this->cache->get(self::VERSION);
        if (! is_string($version)) {
            // A flushed cache: a fresh version makes every worker reload once, which is harmless.
            $version = (string) Str::uuid();
            $this->cache->forever(self::VERSION, $version);
        }

        return $version;
    }

    public function has(OperationalSetting $setting): bool
    {
        return array_key_exists($setting->value, $this->rows());
    }

    public function recordMailTest(bool $sent): void
    {
        $this->cache->forever(self::LAST_TEST, $sent);
    }

    public function lastMailTest(): ?bool
    {
        $sent = $this->cache->get(self::LAST_TEST);

        return is_bool($sent) ? $sent : null;
    }

    /** @return array<string, array{0: string, 1: bool}> */
    private function rows(): array
    {
        /** @var array<string, array{0: string, 1: bool}> */
        return $this->cache->rememberForever(self::ROWS, fn () => OperationalSettingRow::query()
            ->get(['key', 'value', 'is_secret'])
            ->mapWithKeys(fn (OperationalSettingRow $row) => [$row->key => [$row->value, $row->is_secret]])
            ->all());
    }
}

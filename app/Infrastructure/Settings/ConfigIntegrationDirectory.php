<?php

namespace App\Infrastructure\Settings;

use App\Domain\Settings\Contracts\IntegrationDirectory;
use Illuminate\Contracts\Config\Repository as Config;

/** Reads config() and keeps only yes/no and the mode: no key ever leaves this class. */
final readonly class ConfigIntegrationDirectory implements IntegrationDirectory
{
    public function __construct(private Config $config, private bool $production) {}

    public function describe(): array
    {
        return [
            $this->entry('google', $this->filled('services.google.client_id', 'services.google.client_secret'), null),
            $this->entry('paypal', $this->filled('services.paypal.client_id', 'services.paypal.client_secret', 'services.paypal.webhook_id'), $this->mode('services.paypal.mode')),
            $this->entry('melhorEnvio', $this->filled('services.melhor_envio.token'), $this->mode('services.melhor_envio.env')),
            $this->entry('umami', $this->filled('services.umami.script_url', 'services.umami.website_id'), null, critical: false),
            $this->entry('geocoder', true, (string) $this->config->get('ovniporto.geocoder', 'nominatim')),
        ];
    }

    /** @return array{name: string, configured: bool, mode: ?string, attention: bool} */
    private function entry(string $name, bool $configured, ?string $mode, bool $critical = true): array
    {
        $sandboxLive = $this->production && $mode === 'sandbox';

        return [
            'name' => $name,
            'configured' => $configured,
            'mode' => $mode,
            'attention' => $sandboxLive || ($critical && ! $configured),
        ];
    }

    private function filled(string ...$keys): bool
    {
        foreach ($keys as $key) {
            if (blank($this->config->get($key))) {
                return false;
            }
        }

        return true;
    }

    private function mode(string $key): string
    {
        return in_array($this->config->get($key), ['live', 'production'], true) ? 'production' : 'sandbox';
    }
}

<?php

namespace App\Infrastructure\Sightings;

use App\Domain\Sightings\Contracts\HistoricalCaseLibrary;
use App\Infrastructure\Cache\FragmentCache;
use InvalidArgumentException;
use JsonException;

/**
 * Reads resources/content/sightings/historical-cases.json, checked once for the shape the pages rely
 * on and cached under its modification time, so an edited file is picked up at once.
 */
final class JsonHistoricalCaseLibrary implements HistoricalCaseLibrary
{
    private const TTL = 86400;

    private const CASE_KEYS = ['slug', 'region', 'title', 'date', 'place', 'summary', 'documentation', 'sources', 'image', 'coordinates'];

    /** @var array<string, mixed>|null */
    private ?array $loaded = null;

    public function __construct(private readonly string $path) {}

    public function collection(): array
    {
        if ($this->loaded !== null) {
            return $this->loaded;
        }
        if (! is_file($this->path)) {
            throw new InvalidArgumentException("Arquivo de casos históricos ausente: {$this->path}");
        }

        return $this->loaded = FragmentCache::remember('historical-cases', (string) filemtime($this->path), self::TTL, function (): array {
            try {
                $data = json_decode((string) file_get_contents($this->path), true, flags: JSON_THROW_ON_ERROR);
            } catch (JsonException $e) {
                throw new InvalidArgumentException("historical-cases.json não é um JSON válido: {$e->getMessage()}", previous: $e);
            }
            $this->validate($data);

            return $data;
        });
    }

    public function slugs(): array
    {
        return array_column($this->collection()['cases'], 'slug');
    }

    private function validate(mixed $data): void
    {
        if (! is_array($data) || ! isset($data['title'], $data['regions'], $data['cases']) || ! is_array($data['cases'])) {
            throw new InvalidArgumentException('historical-cases.json precisa de title, regions e cases');
        }
        foreach ($data['cases'] as $i => $case) {
            $missing = array_diff(self::CASE_KEYS, array_keys((array) $case));
            if ($missing !== []) {
                throw new InvalidArgumentException("cases[{$i}] sem o campo: ".implode(', ', $missing));
            }
            if (! isset($data['regions'][$case['region']])) {
                throw new InvalidArgumentException("cases[{$i}] com região desconhecida: {$case['region']}");
            }
        }
    }
}

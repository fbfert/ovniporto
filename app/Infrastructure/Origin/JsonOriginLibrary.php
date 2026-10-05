<?php

namespace App\Infrastructure\Origin;

use App\Domain\Origin\ConfidenceGrade;
use App\Domain\Origin\Contracts\OriginLibrary;
use App\Domain\Origin\InvalidOriginData;
use App\Infrastructure\Cache\FragmentCache;
use JsonException;

/**
 * Reads resources/content/origin/{cachi,atlas,images}.json. Each file is checked once for the shape
 * the pages rely on and cached under its modification time, so an edited file is picked up at once
 * (arrays only: the cache store never unserializes objects).
 */
final class JsonOriginLibrary implements OriginLibrary
{
    private const TTL = 86400;

    /** @var array<string, array<mixed>> */
    private array $loaded = [];

    public function __construct(private readonly string $directory) {}

    public function cachi(): array
    {
        return $this->load('cachi', function (array $data): void {
            $this->requireKeys($data, ['title', 'chapters'], 'cachi');
            foreach ($data['chapters'] as $i => $chapter) {
                $this->requireKeys($chapter, ['id', 'eyebrow', 'title', 'body'], "cachi.chapters[{$i}]");
            }
        });
    }

    public function atlas(): array
    {
        return $this->load('atlas', function (array $data): void {
            $this->requireKeys($data, ['title', 'grades', 'cases', 'sources', 'comparativeTimeline', 'candidates'], 'atlas');
            foreach ($data['cases'] as $i => $case) {
                $this->requireKeys($case, ['slug', 'number', 'name', 'country', 'seal', 'facts', 'sources', 'openQuestions'], "atlas.cases[{$i}]");
                ConfidenceGrade::parseSeal((string) $case['seal']);
            }
        });
    }

    public function credits(): array
    {
        $credits = $this->load('images', function (array $data): void {
            foreach ($data as $i => $image) {
                $this->requireKeys($image, ['slug', 'alt', 'author', 'license', 'sourceUrl', 'kind'], "images[{$i}]");
            }
        });

        return array_column($credits, null, 'slug');
    }

    public function caseSlugs(): array
    {
        return array_column($this->atlas()['cases'], 'slug');
    }

    /**
     * @param  callable(array<mixed>): void  $validate
     * @return array<mixed>
     */
    private function load(string $name, callable $validate): array
    {
        if (isset($this->loaded[$name])) {
            return $this->loaded[$name];
        }
        $path = "{$this->directory}/{$name}.json";
        if (! is_file($path)) {
            throw new InvalidOriginData("Arquivo de origem ausente: {$name}.json");
        }

        return $this->loaded[$name] = FragmentCache::remember(
            'origin',
            $name.':'.filemtime($path),
            self::TTL,
            function () use ($path, $name, $validate): array {
                try {
                    $data = json_decode((string) file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
                } catch (JsonException $e) {
                    throw new InvalidOriginData("{$name}.json não é um JSON válido: {$e->getMessage()}", previous: $e);
                }
                if (! is_array($data)) {
                    throw new InvalidOriginData("{$name}.json deve conter um objeto ou uma lista");
                }
                $validate($data);

                return $data;
            },
        );
    }

    /**
     * @param  mixed  $data  any decoded JSON value; only an object with every key passes
     * @param  list<string>  $keys
     */
    private function requireKeys(mixed $data, array $keys, string $where): void
    {
        if (! is_array($data)) {
            throw new InvalidOriginData("{$where} deve ser um objeto");
        }
        $missing = array_values(array_diff($keys, array_keys($data)));
        if ($missing !== []) {
            throw new InvalidOriginData("{$where} sem o campo: ".implode(', ', $missing));
        }
    }
}

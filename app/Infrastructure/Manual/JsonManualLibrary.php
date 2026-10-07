<?php

namespace App\Infrastructure\Manual;

use App\Domain\Content\Contracts\MarkdownRenderer;
use App\Domain\Manual\Contracts\ManualLibrary;
use App\Domain\Manual\InvalidManualData;
use App\Domain\Manual\ManualGroup;
use App\Domain\Manual\MapNodeKind;
use App\Domain\Panel\PanelArea;
use App\Infrastructure\Cache\FragmentCache;
use JsonException;

/**
 * Reads resources/content/manual/<slug>.json. Every chapter is checked for the shape the panel relies on
 * (a broken file fails loudly, naming chapter and node) and cached under the files' modification times,
 * so an edited chapter shows up at once.
 */
final class JsonManualLibrary implements ManualLibrary
{
    private const TTL = 86400;

    /** The radial map stays legible up to this many branches, each with this many leaves. */
    public const MAX_BRANCHES = 7;

    public const MAX_LEAVES = 8;

    /** @var list<array<string, mixed>>|null */
    private ?array $loaded = null;

    public function __construct(
        private readonly string $directory,
        private readonly MarkdownRenderer $markdown,
    ) {}

    public function chapters(): array
    {
        if ($this->loaded !== null) {
            return $this->loaded;
        }
        $files = glob("{$this->directory}/*.json") ?: [];
        if ($files === []) {
            throw new InvalidManualData("Nenhum capítulo do manual em {$this->directory}");
        }
        $version = md5(implode('|', array_map(fn (string $f) => basename($f).':'.filemtime($f), $files)));

        /** @var list<array{slug: string, area: string|null, group: string, order: int, title: string, summary: string, reviewedAt: string, routes: list<string>, map: array<string, mixed>, sections: list<array{id: string, title: string, screens: list<string>, html: string}>}> $chapters */
        $chapters = FragmentCache::remember('manual', $version, self::TTL, function () use ($files): array {
            $chapters = array_map(fn (string $file) => $this->chapter($file), $files);
            usort($chapters, fn (array $a, array $b) => [$this->groupIndex($a['group']), $a['order']] <=> [$this->groupIndex($b['group']), $b['order']]);

            return $chapters;
        });

        return $this->loaded = $chapters;
    }

    /** @return array<string, mixed> */
    private function chapter(string $file): array
    {
        $slug = basename($file, '.json');
        try {
            $data = json_decode((string) file_get_contents($file), true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new InvalidManualData("{$slug}.json não é um JSON válido: {$e->getMessage()}", previous: $e);
        }
        $this->requireKeys($data, ['slug', 'area', 'group', 'order', 'title', 'summary', 'reviewedAt', 'routes', 'map', 'sections'], $slug);
        /** @var array<string, mixed> $data */
        if ($data['slug'] !== $slug) {
            throw new InvalidManualData("{$slug}: o campo slug deve ser igual ao nome do arquivo");
        }
        foreach (['title', 'summary'] as $text) {
            if (! is_string($data[$text]) || trim($data[$text]) === '') {
                throw new InvalidManualData("{$slug}: o campo {$text} não pode ficar vazio");
            }
        }
        if ($data['area'] !== null && PanelArea::tryFrom((string) $data['area']) === null) {
            throw new InvalidManualData("{$slug}: área desconhecida: {$data['area']}");
        }
        if (ManualGroup::tryFrom((string) $data['group']) === null) {
            throw new InvalidManualData("{$slug}: grupo desconhecido: {$data['group']}");
        }
        $reviewed = \DateTimeImmutable::createFromFormat('!Y-m-d', (string) $data['reviewedAt']);
        if ($reviewed === false || $reviewed->format('Y-m-d') !== $data['reviewedAt']) {
            throw new InvalidManualData("{$slug}: reviewedAt deve ser uma data AAAA-MM-DD");
        }
        if (! is_array($data['routes']) || ! array_is_list($data['routes'])) {
            throw new InvalidManualData("{$slug}: routes deve ser uma lista de nomes de rota");
        }

        $sections = $this->sections($slug, $data['sections']);
        $this->checkMap($slug, $data['map'], array_column($sections, 'id'));

        return [
            'slug' => $slug,
            'area' => $data['area'],
            'group' => $data['group'],
            'order' => (int) $data['order'],
            'title' => (string) $data['title'],
            'summary' => (string) $data['summary'],
            'reviewedAt' => $data['reviewedAt'],
            'routes' => array_map('strval', $data['routes']),
            'map' => $data['map'],
            'sections' => $sections,
        ];
    }

    /** @return list<array{id: string, title: string, screens: list<string>, html: string}> */
    private function sections(string $slug, mixed $sections): array
    {
        if (! is_array($sections) || $sections === [] || ! array_is_list($sections)) {
            throw new InvalidManualData("{$slug}: sections deve ser uma lista não vazia");
        }
        $result = [];
        foreach ($sections as $i => $section) {
            $this->requireKeys($section, ['id', 'title', 'body'], "{$slug}.sections[{$i}]");
            /** @var array<string, mixed> $section */
            $id = (string) $section['id'];
            if (! preg_match('/^[a-z0-9-]+$/', $id)) {
                throw new InvalidManualData("{$slug}.sections[{$i}]: id inválido: {$id}");
            }
            if (isset($result[$id])) {
                throw new InvalidManualData("{$slug}: seção repetida: {$id}");
            }
            $screen = $section['screen'] ?? null;
            if ($screen !== null && ! is_string($screen) && ! (is_array($screen) && array_is_list($screen) && array_filter($screen, 'is_string') === $screen)) {
                throw new InvalidManualData("{$slug}.sections[{$i}]: screen deve ser um nome de rota ou uma lista de nomes");
            }
            $result[$id] = [
                'id' => $id,
                'title' => (string) $section['title'],
                // "screen" names the page (or pages, e.g. create and edit) this section explains.
                'screens' => (array) $screen,
                'html' => $this->markdown->render((string) $section['body']),
            ];
        }

        return array_values($result);
    }

    /** @param list<string> $sectionIds */
    private function checkMap(string $slug, mixed $map, array $sectionIds): void
    {
        $this->requireKeys($map, ['label', 'children'], "{$slug}.map");
        /** @var array{label: string, children: mixed} $map */
        $branches = $this->children($slug, $map, self::MAX_BRANCHES);
        foreach ($branches as $branch) {
            $this->checkNode($slug, $branch, $sectionIds);
            foreach ($this->children($slug, $branch, self::MAX_LEAVES) as $leaf) {
                $this->checkNode($slug, $leaf, $sectionIds);
                if (($leaf['children'] ?? []) !== []) {
                    throw new InvalidManualData("{$slug}: o nó \"{$leaf['label']}\" passa de dois níveis");
                }
            }
        }
    }

    /**
     * @param  array<string, mixed>  $node
     * @return list<array<string, mixed>>
     */
    private function children(string $slug, array $node, int $max): array
    {
        $children = $node['children'] ?? [];
        if (! is_array($children) || ! array_is_list($children)) {
            throw new InvalidManualData("{$slug}: children de \"{$node['label']}\" deve ser uma lista");
        }
        if (count($children) > $max) {
            throw new InvalidManualData("{$slug}: \"{$node['label']}\" tem ".count($children)." ramos (máximo {$max})");
        }

        return $children;
    }

    /**
     * @param  array<string, mixed>  $node
     * @param  list<string>  $sectionIds
     */
    private function checkNode(string $slug, mixed $node, array $sectionIds): void
    {
        $this->requireKeys($node, ['label', 'kind'], "{$slug}.map");
        /** @var array{label: string, kind: string, section?: string} $node */
        if (MapNodeKind::tryFrom($node['kind']) === null) {
            throw new InvalidManualData("{$slug}: o nó \"{$node['label']}\" tem tipo desconhecido: {$node['kind']}");
        }
        if (isset($node['section']) && ! in_array($node['section'], $sectionIds, true)) {
            throw new InvalidManualData("{$slug}: o nó \"{$node['label']}\" aponta para a seção inexistente \"{$node['section']}\"");
        }
    }

    private function groupIndex(string $group): int
    {
        return (int) array_search(ManualGroup::from($group), ManualGroup::cases(), true);
    }

    /** @param list<string> $keys */
    private function requireKeys(mixed $data, array $keys, string $where): void
    {
        if (! is_array($data)) {
            throw new InvalidManualData("{$where} deve ser um objeto");
        }
        $missing = array_values(array_diff($keys, array_keys($data)));
        if ($missing !== []) {
            throw new InvalidManualData("{$where} sem o campo: ".implode(', ', $missing));
        }
    }
}

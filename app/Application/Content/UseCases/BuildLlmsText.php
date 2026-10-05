<?php

namespace App\Application\Content\UseCases;

use App\Domain\Origin\Contracts\OriginLibrary;

/**
 * /llms.txt: a plain-text guide for AI assistants (llmstxt.org), built from the same data the pages show.
 * It leads with the Cachi dossier, the page meant to be cited as a reference.
 */
final readonly class BuildLlmsText
{
    /** Other pages worth reading, by route name. */
    private const PAGES = [
        'origin' => '/origem',
        'origin.relato' => '/origem/relato',
        'origin.atlas' => '/origem/atlas',
        'place' => '/o-lugar',
        'faq' => '/faq',
    ];

    public function __construct(private OriginLibrary $origin) {}

    public function execute(string $baseUrl): string
    {
        $dossier = $this->origin->cachi();
        $chapters = array_column($dossier['chapters'], null, 'id');

        return implode("\n", [
            '# '.$this->text('site_name'),
            '',
            '> '.$this->text('default_description'),
            '',
            $this->text('llms.about'),
            '',
            "## {$dossier['reference']['name']}",
            '',
            "- [{$dossier['reference']['headline']}]({$baseUrl}/origem/cachi): ".$this->text('pages.origin.cachi.description'),
            '',
            $dossier['lead'],
            '',
            '### '.$dossier['summary']['title'],
            '',
            ...$this->questions($dossier['summary']['items']),
            '### '.$chapters['linha-do-tempo']['title'],
            '',
            ...array_map(fn (array $m) => "- {$m['year']}: {$m['title']}. {$m['body']}", $chapters['linha-do-tempo']['timeline']),
            '',
            '### '.$chapters['fontes']['title'],
            '',
            ...array_map(fn (array $s) => "- [{$s['title']}]({$s['url']}): {$s['publisher']}, {$s['date']}", $chapters['fontes']['sources']),
            '',
            '## '.$this->text('llms.pages'),
            '',
            ...array_map(
                fn (string $route, string $path) => '- ['.$this->text("pages.{$route}.title")."]({$baseUrl}{$path}): ".$this->text("pages.{$route}.description"),
                array_keys(self::PAGES),
                self::PAGES,
            ),
            '',
        ]);
    }

    /**
     * @param  list<array{question: string, answer: string}>  $items
     * @return list<string>
     */
    private function questions(array $items): array
    {
        $lines = [];
        foreach ($items as $item) {
            array_push($lines, "**{$item['question']}**", $item['answer'], '');
        }

        return $lines;
    }

    private function text(string $key): string
    {
        // Route names contain dots, so pages are read as an array instead of a nested translation key.
        if (str_starts_with($key, 'pages.')) {
            [$route, $field] = [substr($key, 6, strrpos($key, '.') - 6), substr($key, strrpos($key, '.') + 1)];

            return (string) (trans('seo.pages', [], 'pt_BR')[$route][$field] ?? '');
        }

        return (string) trans("seo.{$key}", [], 'pt_BR');
    }
}

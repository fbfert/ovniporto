<?php

namespace App\Application\Content\UseCases;

use App\Domain\Content\Contracts\ContentBlockRepository;
use App\Domain\Privacy\Contracts\PrivacyPractices;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * Privacy policy or terms: the text, a table of contents built from its
 * level-2 headings, the update date and whether it is still a draft. The draft
 * notice only goes away when "{kind}_final" is set by the person in charge.
 */
final readonly class GetLegalPage
{
    public const KINDS = ['privacy', 'terms'];

    public const PRACTICES_ANCHOR = 'o-que-fazemos-na-pratica';

    public function __construct(
        private ContentBlockRepository $blocks,
        private RenderContentBlocks $render,
        private PrivacyPractices $practices,
    ) {}

    /**
     * The privacy page also gets "O que fazemos na prática", first in the index:
     * guarantees from the code, valid even while the legal text is a draft.
     *
     * @return array{html: string, toc: list<array{id: string, title: string}>, updatedAt: ?string, draft: bool, practices: ?array{title: string, items: list<string>}}
     */
    public function execute(string $kind): array
    {
        if (! in_array($kind, self::KINDS, true)) {
            throw new InvalidArgumentException("Unknown legal page: {$kind}");
        }

        $values = $this->blocks->values(["{$kind}_body", "{$kind}_final", "{$kind}_updated_at"]);
        [$html, $toc] = $this->anchorHeadings($this->render->render($values["{$kind}_body"]) ?? '');
        $practices = $kind === 'privacy' ? ['title' => $this->practices->title(), 'items' => $this->practices->all()] : null;
        if ($practices !== null) {
            array_unshift($toc, ['id' => self::PRACTICES_ANCHOR, 'title' => $practices['title']]);
        }

        return [
            'html' => $html,
            'toc' => $toc,
            'updatedAt' => blank($values["{$kind}_updated_at"]) ? null : $values["{$kind}_updated_at"],
            'draft' => blank($values["{$kind}_final"]),
            'practices' => $practices,
        ];
    }

    /** @return array{string, list<array{id: string, title: string}>} */
    private function anchorHeadings(string $html): array
    {
        $toc = [];
        $html = (string) preg_replace_callback('/<h2>(.*?)<\/h2>/s', function (array $match) use (&$toc): string {
            $title = html_entity_decode(strip_tags($match[1]), ENT_QUOTES | ENT_HTML5);
            $id = Str::slug($title);
            $toc[] = ['id' => $id, 'title' => $title];

            return "<h2 id=\"{$id}\">{$match[1]}</h2>";
        }, $html);

        return [$html, $toc];
    }
}

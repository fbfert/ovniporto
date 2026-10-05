<?php

namespace App\Application\Origin\UseCases;

use App\Application\Content\UseCases\RenderContentBlocks;

/**
 * The relato of Julean's yellow car: an editable block (it may still be empty), shown whole on
 * /origem/relato and by its opening paragraphs on the home and on /origem.
 */
final readonly class GetRelato
{
    /** Editable block with the relato (key kept from the former /lenda page). */
    public const BLOCK = 'legend_body';

    /** Paragraphs that open the relato on the pages that only call it. */
    public const OPENING_PARAGRAPHS = 3;

    /** A paragraph this short is a beat of the story and is set larger on the page. */
    private const BEAT_MAX_CHARS = 12;

    public function __construct(private RenderContentBlocks $blocks) {}

    /** @return array{html: string|null, opening: list<string>|null} both null while the relato is not written */
    public function execute(): array
    {
        $html = $this->blocks->execute([self::BLOCK])[self::BLOCK];

        return $html === null
            ? ['html' => null, 'opening' => null]
            : ['html' => $this->markBeats($html), 'opening' => $this->opening($html)];
    }

    /** @return list<string> */
    private function opening(string $html): array
    {
        preg_match_all('#<p>(.*?)</p>#s', $html, $paragraphs);

        return array_slice(array_map(
            fn (string $p) => trim(html_entity_decode(strip_tags($p), ENT_QUOTES | ENT_HTML5)),
            $paragraphs[1],
        ), 0, self::OPENING_PARAGRAPHS);
    }

    private function markBeats(string $html): string
    {
        return (string) preg_replace_callback(
            '#<p>(.*?)</p>#s',
            fn (array $p) => mb_strlen(trim(html_entity_decode(strip_tags($p[1]), ENT_QUOTES | ENT_HTML5))) <= self::BEAT_MAX_CHARS
                ? "<p class=\"relato-beat\">{$p[1]}</p>"
                : $p[0],
            $html,
        );
    }
}

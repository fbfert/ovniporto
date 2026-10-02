<?php

namespace App\Domain\Content;

/**
 * The texts and settings the admin edits in /painel/configuracoes, all kept
 * as content blocks. Anything left empty keeps its honest placeholder on the
 * site ("aguardando conteúdo", "em breve").
 */
final class SiteSettings
{
    public const LINKS = ['link_whatsapp', 'link_instagram', 'contact_email'];

    /** Launch date and the 6-month goals the dashboard measures from it. */
    public const GOALS = ['launch_date', 'goal_members', 'goal_sightings', 'goal_orders'];

    /** Markdown blocks, with a preview before saving. */
    public const BLOCKS = ['home_intro', 'home_place', 'home_store', 'home_legend', 'legend_body', 'privacy_body', 'terms_body'];

    /** Legal texts carry a "final" flag and an update date next to the body. */
    public const LEGAL = ['privacy_body' => 'privacy', 'terms_body' => 'terms'];

    public const LISTS = ['faq', 'regras'];

    public static function isBlock(string $key): bool
    {
        return in_array($key, self::BLOCKS, true);
    }

    /** @return list<string> every key the settings screen reads */
    public static function allKeys(): array
    {
        $legal = [];
        foreach (self::LEGAL as $kind) {
            $legal[] = "{$kind}_final";
            $legal[] = "{$kind}_updated_at";
        }

        return [...self::LINKS, ...self::GOALS, ...self::BLOCKS, ...$legal];
    }
}

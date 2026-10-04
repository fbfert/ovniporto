<?php

namespace App\Application\Privacy\UseCases;

use App\Domain\Content\Contracts\ContentBlockRepository;

/**
 * The version members agree to at /boas-vindas covers the terms and the privacy
 * policy: it is the latest "atualizado em" date among the two texts marked final.
 * Drafts don't count (the panel dates every save), so while both are drafts the
 * version is "inicial".
 */
final readonly class CurrentTermsVersion
{
    public const INITIAL = 'inicial';

    private const KINDS = ['terms', 'privacy'];

    public function __construct(private ContentBlockRepository $blocks) {}

    public function execute(): string
    {
        $keys = array_merge(...array_map(fn (string $kind) => ["{$kind}_final", "{$kind}_updated_at"], self::KINDS));
        $values = $this->blocks->values($keys);

        $dates = [];
        foreach (self::KINDS as $kind) {
            if (filled($values["{$kind}_final"]) && filled($values["{$kind}_updated_at"])) {
                $dates[] = (string) $values["{$kind}_updated_at"];
            }
        }

        return $dates === [] ? self::INITIAL : max($dates);
    }
}

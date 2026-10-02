<?php

namespace App\Application\Content\UseCases;

use App\Domain\Content\Contracts\ContentBlockRepository;

/**
 * Editable texts of the home page. Blocks that were never written fall back to an
 * empty string, except the legend, which stays null so the UI can show "aguardando conteúdo".
 */
final readonly class GetHomeContent
{
    public const KEYS = [
        'home_intro',
        'home_place',
        'home_store',
        'home_legend',
        'legend_body',
    ];

    public function __construct(private ContentBlockRepository $blocks) {}

    /** @return array<string, string|null> */
    public function execute(): array
    {
        $values = $this->blocks->values(self::KEYS);

        foreach ($values as $key => $value) {
            $values[$key] = $key === 'legend_body'
                ? (blank($value) ? null : $value)
                : ($value ?? '');
        }

        return $values;
    }
}

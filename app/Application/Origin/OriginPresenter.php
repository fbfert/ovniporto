<?php

namespace App\Application\Origin;

use App\Domain\Origin\ConfidenceGrade;
use App\Domain\Origin\SourceKind;

/** Shared wording for the origin pages: grades and source kinds always travel with their words. */
final class OriginPresenter
{
    /** @return list<array{grade: string, meaning: string}> */
    public static function seal(string $seal): array
    {
        return array_map(
            fn (ConfidenceGrade $grade) => ['grade' => $grade->value, 'meaning' => $grade->meaning()],
            ConfidenceGrade::parseSeal($seal),
        );
    }

    /** @return array{kind: string, label: string} */
    public static function kind(string $kind): array
    {
        return ['kind' => $kind, 'label' => SourceKind::from($kind)->label()];
    }

    /**
     * Credits of the images a page uses, in the order they are given.
     *
     * @param  array<string, array<string, mixed>>  $credits
     * @param  list<string>  $slugs
     * @return array<string, array<string, mixed>>
     */
    public static function credits(array $credits, array $slugs): array
    {
        $used = [];
        foreach (array_unique($slugs) as $slug) {
            if (isset($credits[$slug])) {
                $used[$slug] = $credits[$slug];
            }
        }

        return $used;
    }

    /**
     * The value of a labelled fact ("Categoria provisória", "Confiança"...), or null.
     *
     * @param  list<array{label: string, value: string}>  $facts
     */
    public static function fact(array $facts, string $labelPrefix): ?string
    {
        foreach ($facts as $fact) {
            if (str_starts_with($fact['label'], $labelPrefix)) {
                return $fact['value'];
            }
        }

        return null;
    }
}

<?php

namespace Tests\Support;

use App\Domain\Origin\Contracts\OriginLibrary;

/** A three-case Atlas and a two-chapter Cachi dossier, enough to exercise the origin use cases. */
final class FakeOriginLibrary implements OriginLibrary
{
    public function cachi(): array
    {
        return [
            'title' => 'Quando uma lenda ganha um lugar',
            'cover' => 'cachi-aereo',
            'chapters' => [
                ['id' => 'a-noite', 'eyebrow' => '2008', 'title' => 'A noite', 'body' => ['…'], 'kinds' => ['voice', 'report']],
                [
                    'id' => 'relatos', 'eyebrow' => 'Arquivo', 'title' => '126 histórias', 'body' => ['…'],
                    'cases' => [['date' => '2011', 'title' => 'Nuvem', 'summary' => '…', 'kind' => 'ordinary', 'image' => 'recta-tin-tin']],
                    'gallery' => [['image' => 'cachi-aereo', 'caption' => 'Do alto']],
                ],
            ],
        ];
    }

    public function atlas(): array
    {
        $case = fn (int $n, string $slug, string $country, string $seal, array $sources) => [
            'slug' => $slug, 'number' => $n, 'name' => ucfirst($slug), 'country' => $country, 'seal' => $seal,
            'facts' => [['label' => 'Categoria provisória', 'value' => "categoria {$slug}"]],
            'sources' => $sources, 'openQuestions' => ['?'], 'image' => ['file' => "atlas-{$slug}"],
            'coordinates' => ['lat' => 1.0, 'lng' => 2.0, 'approximate' => false],
        ];

        return [
            'title' => 'Atlas', 'grades' => [['grade' => 'A', 'use' => 'x'], ['grade' => 'F', 'use' => 'y']],
            'cases' => [
                $case(1, 'st-paul', 'Canadá', 'A/B', ['stpaul-town']),
                $case(2, 'cachi', 'Argentina', 'B/C', ['la-nacion', 'missing-id']),
                $case(3, 'lages', 'Brasil', 'F', []),
            ],
            'sources' => [
                ['id' => 'stpaul-town', 'title' => 'The Landing Pad', 'url' => 'https://example.org/stpaul'],
                ['id' => 'la-nacion', 'title' => 'Cachi', 'url' => 'https://example.org/cachi'],
            ],
            'comparativeTimeline' => [['date' => '1967', 'place' => 'St. Paul', 'event' => 'inauguração']],
            'candidates' => [['name' => 'Houten']],
        ];
    }

    public function credits(): array
    {
        $credit = fn (string $slug, string $kind = 'photo') => ['slug' => $slug, 'alt' => "alt {$slug}", 'author' => 'Autor', 'license' => 'CC BY 2.0', 'sourceUrl' => "https://commons.example/{$slug}", 'kind' => $kind];

        return [
            'cachi-aereo' => $credit('cachi-aereo'),
            'recta-tin-tin' => $credit('recta-tin-tin'),
            'atlas-st-paul' => $credit('atlas-st-paul'),
            'atlas-lages' => $credit('atlas-lages', 'location-map'),
        ];
    }

    public function caseSlugs(): array
    {
        return array_column($this->atlas()['cases'], 'slug');
    }
}

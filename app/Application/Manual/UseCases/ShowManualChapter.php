<?php

namespace App\Application\Manual\UseCases;

use App\Domain\Manual\Contracts\ManualLibrary;
use App\Domain\Members\MemberRole;

/**
 * One chapter with its map and sections, plus the previous and next chapters this role may read.
 * A chapter of an area the role does not open is reported as missing, not as forbidden.
 */
final readonly class ShowManualChapter
{
    public function __construct(private ManualLibrary $library, private ListManualChapters $list) {}

    /** @return array<string, mixed>|null */
    public function execute(MemberRole $role, string $slug): ?array
    {
        $chapter = array_values(array_filter($this->library->chapters(), fn (array $c) => $c['slug'] === $slug))[0] ?? null;
        if ($chapter === null || ! ManualAccess::opens($role, $chapter['area'])) {
            return null;
        }
        $readable = $this->list->execute($role);
        $position = (int) array_search($slug, array_column($readable, 'slug'), true);
        $link = fn (?array $c) => $c === null ? null : ['slug' => $c['slug'], 'title' => $c['title']];

        return [
            'chapter' => [
                'slug' => $chapter['slug'],
                'title' => $chapter['title'],
                'summary' => $chapter['summary'],
                'reviewedAt' => $chapter['reviewedAt'],
                'map' => $chapter['map'],
                'sections' => array_map(fn (array $s) => ['id' => $s['id'], 'title' => $s['title'], 'html' => $s['html']], $chapter['sections']),
            ],
            'previous' => $link($readable[$position - 1] ?? null),
            'next' => $link($readable[$position + 1] ?? null),
        ];
    }
}

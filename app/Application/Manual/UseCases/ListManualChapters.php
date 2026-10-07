<?php

namespace App\Application\Manual\UseCases;

use App\Domain\Manual\Contracts\ManualLibrary;
use App\Domain\Members\MemberRole;

/** The manual's index: the chapters this role may read, without their bodies. */
final readonly class ListManualChapters
{
    public function __construct(private ManualLibrary $library) {}

    /** @return list<array{slug: string, group: string, title: string, summary: string, reviewedAt: string}> */
    public function execute(MemberRole $role): array
    {
        $chapters = array_filter($this->library->chapters(), fn (array $chapter) => ManualAccess::opens($role, $chapter['area']));

        return array_values(array_map(fn (array $chapter) => [
            'slug' => $chapter['slug'],
            'group' => $chapter['group'],
            'title' => $chapter['title'],
            'summary' => $chapter['summary'],
            'reviewedAt' => $chapter['reviewedAt'],
        ], $chapters));
    }
}

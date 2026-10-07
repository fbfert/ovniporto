<?php

namespace App\Application\Manual\UseCases;

use App\Domain\Manual\Contracts\ManualLibrary;
use App\Domain\Members\MemberRole;

/** "Como funciona" on a panel screen: the manual section that explains the page with this route name. */
final readonly class FindManualSection
{
    public function __construct(private ManualLibrary $library) {}

    public function execute(MemberRole $role, string $routeName): ?string
    {
        foreach ($this->library->chapters() as $chapter) {
            if (! ManualAccess::opens($role, $chapter['area'])) {
                continue;
            }
            foreach ($chapter['sections'] as $section) {
                if (in_array($routeName, $section['screens'], true)) {
                    return "/painel/manual/{$chapter['slug']}#{$section['id']}";
                }
            }
        }

        return null;
    }
}

<?php

namespace App\Domain\Manual\Contracts;

/**
 * The panel's operations manual: one chapter per file, versioned with the code it explains.
 * Read-only; bodies come back as safe HTML.
 */
interface ManualLibrary
{
    /**
     * Every chapter, in reading order.
     *
     * @return list<array{
     *     slug: string, area: string|null, group: string, order: int, title: string, summary: string,
     *     reviewedAt: string, routes: list<string>, map: array<string, mixed>,
     *     sections: list<array{id: string, title: string, screens: list<string>, html: string}>
     * }>
     */
    public function chapters(): array;
}

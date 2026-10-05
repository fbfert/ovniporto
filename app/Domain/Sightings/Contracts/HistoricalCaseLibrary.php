<?php

namespace App\Domain\Sightings\Contracts;

/**
 * Historical sightings researched by the team (Santa Catarina, Brazil and the world), shown in the
 * Livro de avistamentos apart from the community's reports. Read-only, versioned with the code.
 */
interface HistoricalCaseLibrary
{
    /** @return array<string, mixed> the whole collection: texts, regions and cases in reading order */
    public function collection(): array;

    /** @return list<string> slugs of the cases, in reading order */
    public function slugs(): array;
}

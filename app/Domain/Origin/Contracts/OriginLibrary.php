<?php

namespace App\Domain\Origin\Contracts;

/**
 * The research behind /origem: the Cachi dossier, the Atlas of ovnipuertos and the credits of every
 * third-party image. Read-only, versioned with the code.
 */
interface OriginLibrary
{
    /** @return array<string, mixed> */
    public function cachi(): array;

    /** @return array<string, mixed> */
    public function atlas(): array;

    /** @return array<string, array<string, mixed>> credits keyed by image slug */
    public function credits(): array;

    /** @return list<string> slugs of the Atlas cases, in dossier order */
    public function caseSlugs(): array;
}

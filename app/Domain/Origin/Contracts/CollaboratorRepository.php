<?php

namespace App\Domain\Origin\Contracts;

use App\Domain\Origin\CollaborationArea;

/** People who offered to help the origin research (Atlas and Cachi dossier). */
interface CollaboratorRepository
{
    /**
     * Stores an application and returns its id.
     *
     * @param  list<CollaborationArea>  $areas
     */
    public function add(string $name, string $email, string $location, array $areas, string $message, string $consentText): int;

    /**
     * Newest first, for the panel and its CSV export.
     *
     * @return list<array{id: int, name: string, email: string, location: string, areas: list<string>, message: string, consentedAt: string}>
     */
    public function collaborators(): array;

    /** The e-mail of the application, or null when it does not exist. */
    public function emailOf(int $id): ?string;

    /** Deletes the application. Returns its e-mail, or null when it did not exist. */
    public function remove(int $id): ?string;
}

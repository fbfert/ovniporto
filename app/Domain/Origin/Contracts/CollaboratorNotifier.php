<?php

namespace App\Domain\Origin\Contracts;

interface CollaboratorNotifier
{
    /** Thanks the person and tells the team a new application is in the panel. Implementations MUST queue both. */
    public function applied(int $collaboratorId, string $name, string $email): void;
}

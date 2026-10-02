<?php

namespace App\Domain\Sightings;

use DomainException;

/** A moderation step that the report's current status, or the missing note, does not allow. */
final class InvalidModeration extends DomainException
{
    public function __construct(public readonly string $field, string $message)
    {
        parent::__construct($message);
    }
}

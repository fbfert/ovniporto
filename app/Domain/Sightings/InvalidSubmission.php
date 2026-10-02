<?php

namespace App\Domain\Sightings;

use DomainException;

/** A domain rule a report breaks; `field` points the form at the right place. */
final class InvalidSubmission extends DomainException
{
    public function __construct(public readonly string $field, string $message)
    {
        parent::__construct($message);
    }
}

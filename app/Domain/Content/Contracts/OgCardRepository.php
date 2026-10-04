<?php

namespace App\Domain\Content\Contracts;

use App\Domain\Content\Sharing\OgCard;
use App\Domain\Content\Sharing\OgKind;

/** Preview data for public content only: pending, inactive or unpublished content returns null. */
interface OgCardRepository
{
    public function find(OgKind $kind, string $key): ?OgCard;
}

<?php

namespace App\Domain\Content\Contracts;

interface ContentBlockRepository
{
    /**
     * @param  list<string>  $keys
     * @return array<string, string|null> Every requested key is present; missing blocks map to null.
     */
    public function values(array $keys): array;
}

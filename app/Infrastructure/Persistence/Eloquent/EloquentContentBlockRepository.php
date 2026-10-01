<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Content\Contracts\ContentBlockRepository;
use App\Models\ContentBlock;

final class EloquentContentBlockRepository implements ContentBlockRepository
{
    public function values(array $keys): array
    {
        /** @var array<string, string|null> $found */
        $found = ContentBlock::query()->whereIn('key', $keys)->pluck('value', 'key')->all();

        $values = [];
        foreach ($keys as $key) {
            $values[$key] = $found[$key] ?? null;
        }

        return $values;
    }
}

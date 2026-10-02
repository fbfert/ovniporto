<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property string|null $excerpt
 * @property string $body
 * @property string|null $cover_path
 * @property string|null $cover_alt
 * @property int $phase
 * @property list<array{path: string, alt?: string}>|null $gallery
 * @property Carbon|null $published_at
 * @property bool $is_demo
 */
class ConstructionPost extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['gallery' => 'array', 'published_at' => 'datetime', 'is_demo' => 'boolean'];
    }

    /**
     * Only posts whose publication date has passed: scheduled ones stay hidden everywhere.
     *
     * @param  Builder<ConstructionPost>  $query
     */
    public function scopePublished(Builder $query): void
    {
        $query->whereNotNull('published_at')->where('published_at', '<=', now());
    }
}

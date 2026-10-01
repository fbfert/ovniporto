<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $type
 * @property string $city
 * @property string|null $cover_path
 * @property bool $is_featured
 * @property bool $is_demo
 * @property Carbon|null $consent_given_at
 * @property Carbon|null $published_at
 */
class RegionPartner extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'gallery' => 'array',
            'is_featured' => 'boolean',
            'is_demo' => 'boolean',
            'consent_given_at' => 'datetime',
            'published_at' => 'datetime',
        ];
    }
}

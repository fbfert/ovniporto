<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $type
 * @property string|null $short_description
 * @property string $city
 * @property string|null $address
 * @property string|null $lat decimal, as returned by the driver
 * @property string|null $lng decimal, as returned by the driver
 * @property string|null $phone
 * @property string|null $whatsapp
 * @property string|null $instagram
 * @property string|null $website
 * @property string|null $cover_path
 * @property list<array{path: string, alt?: string}>|null $gallery
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

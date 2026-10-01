<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

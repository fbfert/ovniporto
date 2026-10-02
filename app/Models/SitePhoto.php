<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $path
 * @property string $alt
 * @property string|null $caption
 * @property Carbon|null $taken_at
 * @property int $sort_order
 */
class SitePhoto extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['taken_at' => 'date'];
    }
}

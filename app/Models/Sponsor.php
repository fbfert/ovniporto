<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string|null $tier
 * @property string|null $logo_path
 * @property string|null $url
 * @property int $sort_order
 */
class Sponsor extends Model
{
    protected $guarded = ['id'];
}

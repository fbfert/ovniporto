<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property int|null $amount_cents
 * @property string|null $reward
 * @property bool $publish_name
 * @property Carbon|null $supported_at
 */
class Supporter extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['publish_name' => 'boolean', 'supported_at' => 'datetime'];
    }
}

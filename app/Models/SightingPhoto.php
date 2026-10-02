<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $sighting_id
 * @property string $path original upload while unprocessed; base path of the WebP variants after
 * @property int $width
 * @property int $height
 * @property list<int>|null $variants
 * @property Carbon|null $processed_at
 * @property int $sort_order
 */
class SightingPhoto extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['variants' => 'array', 'processed_at' => 'datetime'];
    }

    /** @return BelongsTo<Sighting, $this> */
    public function sighting(): BelongsTo
    {
        return $this->belongsTo(Sighting::class);
    }
}

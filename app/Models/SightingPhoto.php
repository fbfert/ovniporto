<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SightingPhoto extends Model
{
    protected $guarded = ['id'];

    /** @return BelongsTo<Sighting, $this> */
    public function sighting(): BelongsTo
    {
        return $this->belongsTo(Sighting::class);
    }
}

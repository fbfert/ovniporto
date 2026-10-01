<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Member extends Model
{
    protected $fillable = ['google_id', 'name', 'email', 'avatar_url', 'nickname', 'city', 'role', 'terms_accepted_at'];

    protected function casts(): array
    {
        return ['terms_accepted_at' => 'datetime'];
    }

    /** @return HasMany<Sighting, $this> */
    public function sightings(): HasMany
    {
        return $this->hasMany(Sighting::class);
    }
}

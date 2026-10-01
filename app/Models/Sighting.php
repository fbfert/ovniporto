<?php

namespace App\Models;

use App\Domain\Sightings\SightingStatus;
use App\Domain\Sightings\SightingType;
use Database\Factories\SightingFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sighting extends Model
{
    /** @use HasFactory<SightingFactory> */
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'type' => SightingType::class,
            'status' => SightingStatus::class,
            'observed_date' => 'date',
            'consent_given_at' => 'datetime',
            'moderated_at' => 'datetime',
            'published_at' => 'datetime',
            'lat' => 'float',
            'lng' => 'float',
            'is_demo' => 'boolean',
        ];
    }

    /** @param Builder<Sighting> $query */
    public function scopeApproved(Builder $query): void
    {
        $query->where('status', SightingStatus::Approved)->whereNotNull('published_at');
    }

    /** @return BelongsTo<Member, $this> */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    /** @return HasMany<SightingPhoto, $this> */
    public function photos(): HasMany
    {
        return $this->hasMany(SightingPhoto::class)->orderBy('sort_order');
    }
}

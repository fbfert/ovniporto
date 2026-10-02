<?php

namespace App\Models;

use App\Domain\Sightings\SightingStatus;
use App\Domain\Sightings\SightingType;
use App\Observers\SightingObserver;
use Database\Factories\SightingFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property SightingType $type
 * @property SightingStatus $status
 * @property string $description
 * @property Carbon $observed_date
 * @property string|null $place_label
 * @property string $public_nickname
 * @property float $lat
 * @property float $lng
 * @property Carbon $consent_given_at
 * @property Carbon|null $published_at
 * @property bool $is_demo
 * @property int|null $member_id
 * @property string|null $moderation_note
 * @property Carbon $created_at
 * @property string $observed_time_kind
 * @property string|null $observed_time_range
 * @property string|null $observed_time
 * @property string|null $gaze_direction
 */
#[ObservedBy(SightingObserver::class)]
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

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $return_request_id
 * @property string|null $from_status
 * @property string $to_status
 * @property int|null $actor_id
 * @property string|null $note
 * @property Carbon $created_at
 */
class ReturnRequestEvent extends Model
{
    public const UPDATED_AT = null;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }

    /** @return BelongsTo<Member, $this> */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'actor_id');
    }
}

<?php

namespace App\Models;

use App\Domain\Orders\ReturnReason;
use App\Domain\Orders\ReturnRequestStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $protocol
 * @property int $order_id
 * @property string|null $name null once the order is anonymized
 * @property string|null $email
 * @property ReturnReason $reason
 * @property string|null $message
 * @property bool $within_withdrawal
 * @property Carbon|null $delivered_at
 * @property ReturnRequestStatus $status
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class ReturnRequest extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'reason' => ReturnReason::class,
            'status' => ReturnRequestStatus::class,
            'within_withdrawal' => 'boolean',
            'delivered_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Order, $this> */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /** @return HasMany<ReturnRequestEvent, $this> */
    public function events(): HasMany
    {
        return $this->hasMany(ReturnRequestEvent::class)->orderBy('id');
    }
}

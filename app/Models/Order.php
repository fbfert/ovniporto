<?php

namespace App\Models;

use App\Domain\Orders\OrderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $number
 * @property int|null $member_id
 * @property string|null $cart_token
 * @property OrderStatus $status
 * @property string|null $customer_name
 * @property string|null $customer_email
 * @property string|null $customer_phone
 * @property string|null $customer_cpf decrypted on read
 * @property array{cep: string, street: string, number: string, complement: ?string, district: string, city: string, state: string}|null $address
 * @property bool $pickup
 * @property string|null $shipping_option_id
 * @property string|null $shipping_carrier
 * @property string|null $shipping_service
 * @property int|null $shipping_days
 * @property int $subtotal_cents
 * @property int $shipping_cents
 * @property int $total_cents
 * @property string|null $payment_order_id
 * @property string|null $payment_capture_id
 * @property Carbon|null $paid_at
 * @property string|null $shipment_id
 * @property string|null $tracking_code
 * @property string|null $tracking_url
 * @property Carbon $created_at
 */
class Order extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'customer_cpf' => 'encrypted',
            'address' => 'array',
            'pickup' => 'boolean',
            'paid_at' => 'datetime',
        ];
    }

    /** @return HasMany<OrderItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /** @return HasMany<OrderEvent, $this> */
    public function events(): HasMany
    {
        return $this->hasMany(OrderEvent::class)->orderBy('id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $order_id
 * @property int|null $product_variant_id
 * @property string $product_name
 * @property string $variant_name
 * @property string $sku
 * @property int $unit_price_cents
 * @property int $quantity
 * @property int $line_cents
 * @property bool $made_to_order
 * @property int $production_days
 * @property int $weight_grams
 */
class OrderItem extends Model
{
    public $timestamps = false;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['made_to_order' => 'boolean'];
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $product_id
 * @property string $path
 * @property string $alt
 * @property int $sort_order
 */
class ProductImage extends Model
{
    protected $guarded = ['id'];
}

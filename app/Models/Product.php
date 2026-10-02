<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string|null $short_description
 * @property int $price_cents
 * @property int|null $compare_price_cents
 * @property string $kind
 * @property int $production_days
 * @property int $weight_grams
 * @property array<string, int>|null $dimensions
 * @property bool $is_active
 * @property bool $is_featured
 * @property string|null $label
 * @property int $sort_order
 */
class Product extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'dimensions' => 'array',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    /** @return HasMany<ProductImage, $this> */
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    /** @return HasMany<ProductVariant, $this> */
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }
}

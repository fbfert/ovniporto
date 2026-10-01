<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Catalog\Contracts\ProductReadRepository;
use App\Domain\Catalog\Data\ProductCard;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;

final class EloquentProductReadRepository implements ProductReadRepository
{
    public function featured(int $limit): array
    {
        return Product::query()
            ->where('is_active', true)
            ->where('is_featured', true)
            ->with('images')
            ->orderBy('sort_order')
            ->limit($limit)
            ->get()
            ->map(function (Product $product) {
                $image = $product->images->first();

                return new ProductCard(
                    id: $product->id,
                    name: $product->name,
                    slug: $product->slug,
                    priceCents: $product->price_cents,
                    comparePriceCents: $product->compare_price_cents,
                    label: $product->label,
                    madeToOrder: $product->kind === 'made_to_order',
                    productionDays: $product->production_days,
                    imageUrl: $image ? Storage::disk('public')->url($image->path) : null,
                    imageAlt: $image?->alt,
                );
            })
            ->values()
            ->all();
    }
}

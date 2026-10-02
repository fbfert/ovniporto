<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Catalog\Contracts\ProductReadRepository;
use App\Domain\Catalog\Data\ProductCard;
use App\Domain\Catalog\Data\PurchasableVariant;
use App\Domain\Catalog\Money;
use App\Domain\Catalog\PurchaseLimit;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

final class EloquentProductReadRepository implements ProductReadRepository
{
    public function featured(int $limit): array
    {
        return $this->cards($this->activeQuery()->where('is_featured', true)->limit($limit));
    }

    public function active(): array
    {
        return $this->cards($this->activeQuery());
    }

    public function findActive(string $slug): ?array
    {
        $product = $this->activeQuery()->with('variants')->where('slug', $slug)->first();
        if ($product === null) {
            return null;
        }

        return [
            ...$this->card($product)->toArray(),
            'description' => $product->description,
            'shortDescription' => $product->short_description,
            'weightGrams' => $product->weight_grams,
            'dimensions' => $product->dimensions,
            'images' => $product->images
                ->map(fn (ProductImage $image) => ['url' => $this->url($image->path), 'alt' => $image->alt])
                ->values()
                ->all(),
            'variants' => $product->variants
                ->sortBy('id')
                ->map(function (ProductVariant $variant) use ($product) {
                    $purchasable = $this->purchasable($variant, $product);

                    return [
                        'id' => $variant->id,
                        'name' => $variant->name,
                        'priceCents' => $purchasable->unitPrice->cents,
                        'max' => PurchaseLimit::of($purchasable),
                    ];
                })
                ->values()
                ->all(),
        ];
    }

    public function related(string $exceptSlug, int $limit): array
    {
        return $this->cards($this->activeQuery()->where('slug', '!=', $exceptSlug)->limit($limit));
    }

    public function variant(int $variantId): ?PurchasableVariant
    {
        return $this->variants([$variantId])[$variantId] ?? null;
    }

    public function variants(array $variantIds): array
    {
        return ProductVariant::query()
            ->with('product.images')
            ->whereIn('id', $variantIds)
            ->get()
            ->mapWithKeys(fn (ProductVariant $v) => [$v->id => $this->purchasable($v, $v->product)])
            ->all();
    }

    private function purchasable(ProductVariant $variant, Product $product): PurchasableVariant
    {
        $image = $product->images->first();

        return new PurchasableVariant(
            variantId: $variant->id,
            productName: $product->name,
            productSlug: $product->slug,
            variantName: $variant->name,
            unitPrice: Money::cents($product->price_cents)->adjustedBy($variant->price_delta_cents),
            madeToOrder: $product->kind === 'made_to_order',
            productionDays: $product->production_days,
            stock: $variant->stock_qty,
            available: $product->is_active && $variant->is_active,
            weightGrams: $product->weight_grams,
            imageUrl: $image ? $this->url($image->path) : null,
            imageAlt: $image?->alt,
        );
    }

    /** @return Builder<Product> */
    private function activeQuery(): Builder
    {
        return Product::query()->where('is_active', true)->with('images')->orderBy('sort_order')->orderBy('id');
    }

    /**
     * @param  Builder<Product>  $query
     * @return list<ProductCard>
     */
    private function cards(Builder $query): array
    {
        return $query->get()->map(fn (Product $product) => $this->card($product))->values()->all();
    }

    private function card(Product $product): ProductCard
    {
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
            imageUrl: $image ? $this->url($image->path) : null,
            imageAlt: $image?->alt,
        );
    }

    private function url(string $path): string
    {
        return Storage::disk('public')->url($path);
    }
}

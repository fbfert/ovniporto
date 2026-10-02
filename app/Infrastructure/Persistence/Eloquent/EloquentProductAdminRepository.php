<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Catalog\Contracts\ProductAdminRepository;
use App\Domain\Catalog\Data\ProductDraft;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class EloquentProductAdminRepository implements ProductAdminRepository
{
    public function all(): array
    {
        return Product::query()
            ->with(['images', 'variants'])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(function (Product $p) {
                $image = $p->images->first();

                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'slug' => $p->slug,
                    'active' => $p->is_active,
                    'priceCents' => $p->price_cents,
                    'madeToOrder' => $p->kind === 'made_to_order',
                    'stock' => $p->kind === 'made_to_order' ? null : (int) $p->variants->sum('stock_qty'),
                    'image' => $image ? Storage::disk('public')->url($image->path) : null,
                ];
            })
            ->values()
            ->all();
    }

    public function find(int $id): ?array
    {
        $p = Product::query()->with(['images', 'variants'])->find($id);
        if ($p === null) {
            return null;
        }
        $history = DB::table('stock_movements')
            ->leftJoin('members', 'members.id', '=', 'stock_movements.actor_id')
            ->whereIn('product_variant_id', $p->variants->pluck('id'))
            ->orderByDesc('stock_movements.id')
            ->limit(50)
            ->get(['stock_movements.*', 'members.nickname as actor']);

        return [
            'id' => $p->id,
            'name' => $p->name,
            'slug' => $p->slug,
            'shortDescription' => $p->short_description,
            'description' => $p->description,
            'priceCents' => $p->price_cents,
            'comparePriceCents' => $p->compare_price_cents,
            'madeToOrder' => $p->kind === 'made_to_order',
            'productionDays' => $p->production_days,
            'weightGrams' => $p->weight_grams,
            'dimensions' => $p->dimensions,
            'label' => $p->label,
            'active' => $p->is_active,
            'featured' => $p->is_featured,
            'variants' => $p->variants->sortBy('id')->map(fn (ProductVariant $v) => [
                'id' => $v->id,
                'name' => $v->name,
                'sku' => $v->sku,
                'priceDeltaCents' => $v->price_delta_cents,
                'stock' => $v->stock_qty,
                'active' => $v->is_active,
            ])->values()->all(),
            'images' => $p->images->map(fn (ProductImage $i) => [
                'id' => $i->id,
                'url' => Storage::disk('public')->url($i->path),
                'alt' => $i->alt,
            ])->values()->all(),
            'stockHistory' => $history->map(fn (object $m) => [
                /** @var object{product_variant_id: int, delta: int, quantity_after: int, reason: string, actor: ?string, created_at: string} $m */
                'variantId' => (int) $m->product_variant_id,
                'delta' => (int) $m->delta,
                'after' => (int) $m->quantity_after,
                'reason' => (string) $m->reason,
                'actor' => $m->actor,
                'at' => (string) $m->created_at,
            ])->values()->all(),
        ];
    }

    public function create(ProductDraft $draft): int
    {
        $base = Str::slug($draft->name) ?: 'produto';
        $slug = $base;
        for ($i = 2; Product::query()->where('slug', $slug)->exists(); $i++) {
            $slug = "{$base}-{$i}";
        }

        return Product::query()->create([
            ...$this->columns($draft),
            'slug' => $slug,
            'sort_order' => (int) Product::query()->max('sort_order') + 1,
        ])->id;
    }

    public function update(int $id, ProductDraft $draft): void
    {
        Product::query()->findOrFail($id)->update($this->columns($draft));
    }

    public function addVariant(int $productId, string $name, string $sku, int $priceDeltaCents, bool $active): int
    {
        return ProductVariant::query()->create([
            'product_id' => $productId,
            'name' => $name,
            'sku' => $sku,
            'price_delta_cents' => $priceDeltaCents,
            'is_active' => $active,
            'stock_qty' => 0,
        ])->id;
    }

    public function updateVariant(int $variantId, string $name, string $sku, int $priceDeltaCents, bool $active): void
    {
        ProductVariant::query()->whereKey($variantId)->update([
            'name' => $name, 'sku' => $sku, 'price_delta_cents' => $priceDeltaCents, 'is_active' => $active,
        ]);
    }

    public function skuTaken(string $sku, ?int $exceptVariantId = null): bool
    {
        return ProductVariant::query()->where('sku', $sku)->when($exceptVariantId, fn ($q) => $q->whereKeyNot($exceptVariantId))->exists();
    }

    public function adjustStock(int $variantId, int $quantity, string $reason, int $actorId): array
    {
        return DB::transaction(function () use ($variantId, $quantity, $reason, $actorId) {
            $variant = ProductVariant::query()->lockForUpdate()->findOrFail($variantId);
            $before = (int) $variant->stock_qty;
            $variant->update(['stock_qty' => $quantity]);
            DB::table('stock_movements')->insert([
                'product_variant_id' => $variantId,
                'delta' => $quantity - $before,
                'quantity_after' => $quantity,
                'reason' => $reason,
                'actor_id' => $actorId,
                'created_at' => now(),
            ]);

            return ['before' => $before, 'after' => $quantity];
        });
    }

    public function addImage(int $productId, string $path, string $alt): int
    {
        return ProductImage::query()->create([
            'product_id' => $productId,
            'path' => $path,
            'alt' => $alt,
            'sort_order' => (int) ProductImage::query()->where('product_id', $productId)->max('sort_order') + 1,
        ])->id;
    }

    public function updateImage(int $imageId, string $alt): void
    {
        ProductImage::query()->whereKey($imageId)->update(['alt' => $alt]);
    }

    public function deleteImage(int $imageId): ?string
    {
        $image = ProductImage::query()->find($imageId);
        $image?->delete();

        return $image?->path;
    }

    public function moveImage(int $imageId, int $direction): void
    {
        DB::transaction(function () use ($imageId, $direction) {
            $productId = ProductImage::query()->whereKey($imageId)->value('product_id');
            /** @var list<int> $ids */
            $ids = ProductImage::query()->where('product_id', $productId)->orderBy('sort_order')->orderBy('id')->pluck('id')->map(fn ($v) => (int) $v)->all();
            $at = array_search($imageId, $ids, true);
            $to = $at === false ? null : $at + ($direction < 0 ? -1 : 1);
            if ($to === null || ! isset($ids[$to])) {
                return;
            }
            [$ids[$at], $ids[$to]] = [$ids[$to], $ids[$at]];
            foreach ($ids as $order => $id) {
                ProductImage::query()->whereKey($id)->update(['sort_order' => $order]);
            }
        });
    }

    /** @return array<string, mixed> */
    private function columns(ProductDraft $d): array
    {
        return [
            'name' => $d->name,
            'short_description' => $d->shortDescription,
            'description' => $d->description,
            'price_cents' => $d->priceCents,
            'compare_price_cents' => $d->comparePriceCents,
            'kind' => $d->madeToOrder ? 'made_to_order' : 'stock',
            'production_days' => $d->madeToOrder ? $d->productionDays : 0,
            'weight_grams' => $d->weightGrams,
            'dimensions' => $d->dimensions,
            'label' => $d->label,
            'is_active' => $d->active,
            'is_featured' => $d->featured,
        ];
    }
}

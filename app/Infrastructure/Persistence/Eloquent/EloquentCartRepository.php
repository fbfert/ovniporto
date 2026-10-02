<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Orders\Contracts\CartRepository;
use App\Domain\Orders\Data\CartOwner;
use Illuminate\Support\Facades\DB;

final class EloquentCartRepository implements CartRepository
{
    public function quantities(CartOwner $owner): array
    {
        $cartId = $this->cartId($owner, create: false);
        if ($cartId === null) {
            return [];
        }

        /** @var array<int, int> $rows */
        $rows = DB::table('cart_items')
            ->where('cart_id', $cartId)
            ->orderBy('id')
            ->pluck('quantity', 'product_variant_id')
            ->map(fn ($q) => (int) $q)
            ->all();

        return $rows;
    }

    public function setQuantity(CartOwner $owner, int $variantId, int $quantity): void
    {
        $cartId = $this->cartId($owner, create: $quantity > 0);
        if ($cartId === null) {
            return;
        }

        if ($quantity <= 0) {
            DB::table('cart_items')->where('cart_id', $cartId)->where('product_variant_id', $variantId)->delete();

            return;
        }

        DB::table('cart_items')->updateOrInsert(
            ['cart_id' => $cartId, 'product_variant_id' => $variantId],
            ['quantity' => $quantity, 'updated_at' => now(), 'created_at' => now()],
        );
        DB::table('carts')->where('id', $cartId)->update(['updated_at' => now()]);
    }

    public function merge(string $visitorToken, int $memberId): void
    {
        DB::transaction(function () use ($visitorToken, $memberId) {
            $visitorCart = DB::table('carts')->where('token', $visitorToken)->value('id');
            if ($visitorCart === null) {
                return;
            }
            $memberCart = $this->cartId(CartOwner::member($memberId), create: true);

            foreach (DB::table('cart_items')->where('cart_id', $visitorCart)->get() as $item) {
                /** @var object{product_variant_id: int, quantity: int} $item */
                $existing = (int) DB::table('cart_items')
                    ->where('cart_id', $memberCart)
                    ->where('product_variant_id', $item->product_variant_id)
                    ->value('quantity');
                DB::table('cart_items')->updateOrInsert(
                    ['cart_id' => $memberCart, 'product_variant_id' => $item->product_variant_id],
                    ['quantity' => $existing + (int) $item->quantity, 'updated_at' => now(), 'created_at' => now()],
                );
            }
            DB::table('carts')->where('id', $visitorCart)->delete();
        });
    }

    private function cartId(CartOwner $owner, bool $create): ?int
    {
        $column = $owner->memberId !== null ? 'member_id' : 'token';
        $value = $owner->memberId ?? $owner->token;
        $id = DB::table('carts')->where($column, $value)->value('id');
        if ($id !== null || ! $create) {
            return $id === null ? null : (int) $id;
        }

        return (int) DB::table('carts')->insertGetId([$column => $value, 'created_at' => now(), 'updated_at' => now()]);
    }
}

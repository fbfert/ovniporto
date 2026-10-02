<?php

namespace App\Application\Orders\UseCases;

use App\Domain\Catalog\Contracts\ProductReadRepository;
use App\Domain\Catalog\Money;
use App\Domain\Catalog\PurchaseLimit;
use App\Domain\Orders\CartRefused;
use App\Domain\Orders\Contracts\CartRepository;
use App\Domain\Orders\Data\CartOwner;

/**
 * The cart, always priced by the server: whatever the browser sends, only a
 * variant id and a quantity are taken, and each line is valued at the price
 * in the catalog right now.
 */
final readonly class ManageCart
{
    public function __construct(
        private CartRepository $carts,
        private ProductReadRepository $catalog,
    ) {}

    /**
     * Lines whose product went inactive are left out (and dropped from the cart).
     *
     * @return array{items: list<array<string, mixed>>, count: int, subtotalCents: int, weightGrams: int}
     */
    public function view(CartOwner $owner): array
    {
        $quantities = $this->carts->quantities($owner);
        $variants = $this->catalog->variants(array_keys($quantities));
        $items = [];
        $subtotal = Money::zero();
        $count = 0;
        $weight = 0;

        foreach ($quantities as $variantId => $quantity) {
            $variant = $variants[$variantId] ?? null;
            $max = $variant === null ? 0 : PurchaseLimit::of($variant);
            if ($variant === null || $max === 0) {
                $this->carts->setQuantity($owner, $variantId, 0);

                continue;
            }
            $quantity = min($quantity, $max);
            $line = $variant->unitPrice->times($quantity);
            $subtotal = $subtotal->plus($line);
            $count += $quantity;
            $weight += $variant->weightGrams * $quantity;
            $items[] = [
                'variantId' => $variantId,
                'productName' => $variant->productName,
                'productSlug' => $variant->productSlug,
                'variantName' => $variant->variantName,
                'unitPriceCents' => $variant->unitPrice->cents,
                'lineCents' => $line->cents,
                'quantity' => $quantity,
                'max' => $max,
                'madeToOrder' => $variant->madeToOrder,
                'productionDays' => $variant->productionDays,
                'image' => $variant->imageUrl,
                'imageAlt' => $variant->imageAlt,
            ];
        }

        return ['items' => $items, 'count' => $count, 'subtotalCents' => $subtotal->cents, 'weightGrams' => $weight];
    }

    /** Adds to what is already there; CartRefused carries the most that fits. */
    public function add(CartOwner $owner, int $variantId, int $quantity): void
    {
        $current = $this->carts->quantities($owner)[$variantId] ?? 0;
        $this->set($owner, $variantId, $current + max(1, $quantity));
    }

    public function update(CartOwner $owner, int $variantId, int $quantity): void
    {
        $quantity <= 0 ? $this->remove($owner, $variantId) : $this->set($owner, $variantId, $quantity);
    }

    public function remove(CartOwner $owner, int $variantId): void
    {
        $this->carts->setQuantity($owner, $variantId, 0);
    }

    /** On sign-in: the visitor's items join the member's cart. */
    public function merge(string $visitorToken, int $memberId): void
    {
        $this->carts->merge($visitorToken, $memberId);
    }

    private function set(CartOwner $owner, int $variantId, int $quantity): void
    {
        $variant = $this->catalog->variant($variantId);
        $max = $variant === null ? 0 : PurchaseLimit::of($variant);
        if ($quantity > $max) {
            throw new CartRefused($max);
        }
        $this->carts->setQuantity($owner, $variantId, $quantity);
    }
}

<?php

use App\Application\Orders\UseCases\ManageCart;
use App\Domain\Catalog\Contracts\ProductReadRepository;
use App\Domain\Catalog\Data\PurchasableVariant;
use App\Domain\Catalog\Money;
use App\Domain\Orders\CartRefused;
use App\Domain\Orders\Contracts\CartRepository;
use App\Domain\Orders\Data\CartOwner;

function variant(int $id, int $cents, ?int $stock = 10, bool $madeToOrder = false, bool $available = true): PurchasableVariant
{
    return new PurchasableVariant($id, "Produto {$id}", "p-{$id}", 'Único', Money::cents($cents), $madeToOrder, $madeToOrder ? 7 : 0, $stock, $available, 20, null, null);
}

function catalog(PurchasableVariant ...$variants): ProductReadRepository
{
    return new class($variants) implements ProductReadRepository
    {
        /** @param list<PurchasableVariant> $all */
        public function __construct(private array $all) {}

        public function featured(int $limit): array
        {
            return [];
        }

        public function active(): array
        {
            return [];
        }

        public function findActive(string $slug): ?array
        {
            return null;
        }

        public function related(string $exceptSlug, int $limit): array
        {
            return [];
        }

        public function variant(int $variantId): ?PurchasableVariant
        {
            return $this->variants([$variantId])[$variantId] ?? null;
        }

        public function variants(array $variantIds): array
        {
            $found = [];
            foreach ($this->all as $v) {
                if (in_array($v->variantId, $variantIds, true)) {
                    $found[$v->variantId] = $v;
                }
            }

            return $found;
        }
    };
}

function memoryCarts(): CartRepository
{
    return new class implements CartRepository
    {
        /** @var array<string, array<int, int>> */
        public array $carts = [];

        public function quantities(CartOwner $owner): array
        {
            return $this->carts[$this->key($owner)] ?? [];
        }

        public function setQuantity(CartOwner $owner, int $variantId, int $quantity): void
        {
            if ($quantity <= 0) {
                unset($this->carts[$this->key($owner)][$variantId]);

                return;
            }
            $this->carts[$this->key($owner)][$variantId] = $quantity;
        }

        public function merge(string $visitorToken, int $memberId): void
        {
            foreach ($this->carts["t:{$visitorToken}"] ?? [] as $id => $qty) {
                $this->carts["m:{$memberId}"][$id] = ($this->carts["m:{$memberId}"][$id] ?? 0) + $qty;
            }
            unset($this->carts["t:{$visitorToken}"]);
        }

        private function key(CartOwner $owner): string
        {
            return $owner->memberId !== null ? "m:{$owner->memberId}" : "t:{$owner->token}";
        }
    };
}

it('values every line at the catalog price', function () {
    $cart = new ManageCart(memoryCarts(), catalog(variant(1, 800), variant(2, 7900, null, madeToOrder: true)));
    $owner = CartOwner::visitor('abc');

    $cart->add($owner, 1, 3);
    $cart->add($owner, 2, 1);
    $view = $cart->view($owner);

    expect($view['subtotalCents'])->toBe(800 * 3 + 7900)
        ->and($view['count'])->toBe(4)
        ->and($view['items'][1]['madeToOrder'])->toBeTrue();
});

it('caps in-stock items at the stock and made-to-order items at 20', function () {
    $cart = new ManageCart(memoryCarts(), catalog(variant(1, 800, 5), variant(2, 7900, null, madeToOrder: true)));
    $owner = CartOwner::member(7);

    $cart->add($owner, 1, 5);
    expect(fn () => $cart->add($owner, 1, 1))->toThrow(CartRefused::class, 'Temos só 5 disponíveis.');
    expect(fn () => $cart->update($owner, 2, 21))->toThrow(CartRefused::class);
    $cart->update($owner, 2, 20);

    expect($cart->view($owner)['count'])->toBe(25);
});

it('merges the visitor cart into the member cart on sign-in', function () {
    $carts = memoryCarts();
    $cart = new ManageCart($carts, catalog(variant(1, 800), variant(2, 1500)));
    $cart->add(CartOwner::member(7), 1, 1);
    $cart->add(CartOwner::visitor('abc'), 1, 2);
    $cart->add(CartOwner::visitor('abc'), 2, 1);

    $cart->merge('abc', 7);

    expect($carts->quantities(CartOwner::member(7)))->toBe([1 => 3, 2 => 1])
        ->and($carts->quantities(CartOwner::visitor('abc')))->toBe([]);
});

it('drops lines whose product went off the shelf', function () {
    $carts = memoryCarts();
    $carts->setQuantity(CartOwner::visitor('abc'), 9, 2);
    $cart = new ManageCart($carts, catalog(variant(9, 800, available: false)));

    expect($cart->view(CartOwner::visitor('abc'))['items'])->toBe([])
        ->and($carts->quantities(CartOwner::visitor('abc')))->toBe([]);
});

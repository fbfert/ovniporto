<?php

namespace App\Application\Catalog\UseCases;

use App\Domain\Audit\Contracts\Auditor;
use App\Domain\Audit\Data\AuditEntry;
use App\Domain\Catalog\Contracts\ProductAdminRepository;
use App\Domain\Catalog\Data\ProductDraft;
use App\Domain\Content\Contracts\ImageLibrary;
use InvalidArgumentException;

/**
 * /painel/produtos (store and admin): products, variants, square photos with
 * alt text, and stock counts that always leave a line in the history.
 */
final readonly class ManageProducts
{
    public const MAX_STOCK = 100_000;

    public function __construct(
        private ProductAdminRepository $products,
        private ImageLibrary $images,
        private Auditor $auditor,
    ) {}

    /** @return list<array<string, mixed>> */
    public function list(): array
    {
        return $this->products->all();
    }

    /** @return array<string, mixed>|null */
    public function find(int $id): ?array
    {
        return $this->products->find($id);
    }

    public function create(int $actorId, ProductDraft $draft): int
    {
        return $this->auditor->audited(
            new AuditEntry($actorId, 'product.created', 'product', 0, ['name' => $draft->name]),
            fn () => null,
            fn () => $this->products->create($draft),
        );
    }

    public function update(int $actorId, int $id, ProductDraft $draft): void
    {
        $this->record($actorId, 'product.updated', $id, [], fn () => $this->products->update($id, $draft));
    }

    public function addVariant(int $actorId, int $productId, string $name, string $sku, int $deltaCents, bool $active): void
    {
        $sku = $this->sku($sku, null);
        $this->record($actorId, 'product.variant_added', $productId, ['sku' => $sku], fn () => $this->products->addVariant($productId, trim($name), $sku, $deltaCents, $active));
    }

    public function updateVariant(int $actorId, int $productId, int $variantId, string $name, string $sku, int $deltaCents, bool $active): void
    {
        $sku = $this->sku($sku, $variantId);
        $this->record($actorId, 'product.variant_updated', $productId, ['variantId' => $variantId], fn () => $this->products->updateVariant($variantId, trim($name), $sku, $deltaCents, $active));
    }

    /** Counting the shelf: the new quantity, why, and who — the history keeps the difference. */
    public function adjustStock(int $actorId, int $productId, int $variantId, int $quantity, string $reason): void
    {
        $reason = trim($reason);
        if (mb_strlen($reason) < 3) {
            throw new InvalidArgumentException('Diga o motivo do ajuste.');
        }
        if ($quantity < 0 || $quantity > self::MAX_STOCK) {
            throw new InvalidArgumentException('Quantidade inválida.');
        }
        $this->record($actorId, 'product.stock_adjusted', $productId, ['variantId' => $variantId, 'quantity' => $quantity, 'reason' => $reason],
            fn () => $this->products->adjustStock($variantId, $quantity, $reason, $actorId));
    }

    /** Every product photo has an alt text; refused without one. */
    public function addImage(int $actorId, int $productId, string $contents, string $alt): void
    {
        $alt = $this->alt($alt);
        $path = $this->images->storeSquare($contents, 'products');
        $this->record($actorId, 'product.image_added', $productId, ['alt' => $alt], fn () => $this->products->addImage($productId, $path, $alt));
    }

    public function updateImage(int $actorId, int $productId, int $imageId, string $alt): void
    {
        $alt = $this->alt($alt);
        $this->record($actorId, 'product.image_updated', $productId, ['imageId' => $imageId], fn () => $this->products->updateImage($imageId, $alt));
    }

    public function deleteImage(int $actorId, int $productId, int $imageId): void
    {
        $this->record($actorId, 'product.image_deleted', $productId, ['imageId' => $imageId], fn () => $this->images->delete($this->products->deleteImage($imageId)));
    }

    public function moveImage(int $actorId, int $productId, int $imageId, int $direction): void
    {
        $this->record($actorId, 'product.image_moved', $productId, ['imageId' => $imageId], fn () => $this->products->moveImage($imageId, $direction));
    }

    private function alt(string $alt): string
    {
        $alt = trim($alt);
        if ($alt === '') {
            throw new InvalidArgumentException('Descreva a foto (texto alternativo).');
        }

        return $alt;
    }

    private function sku(string $sku, ?int $exceptVariantId): string
    {
        $sku = strtoupper(trim($sku));
        if (! preg_match('/^[A-Z0-9-]{3,40}$/', $sku)) {
            throw new InvalidArgumentException('SKU com 3 a 40 letras, números ou hífen.');
        }
        if ($this->products->skuTaken($sku, $exceptVariantId)) {
            throw new InvalidArgumentException('Esse SKU já existe.');
        }

        return $sku;
    }

    /** @param array<string, mixed> $context */
    private function record(int $actorId, string $action, int $productId, array $context, callable $change): void
    {
        $this->auditor->audited(new AuditEntry($actorId, $action, 'product', $productId, $context), fn () => null, $change);
    }
}

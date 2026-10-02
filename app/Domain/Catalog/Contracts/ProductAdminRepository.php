<?php

namespace App\Domain\Catalog\Contracts;

use App\Domain\Catalog\Data\ProductDraft;

/** The catalog as the store role edits it: every product, active or not. */
interface ProductAdminRepository
{
    /** @return list<array{id: int, name: string, slug: string, active: bool, priceCents: int, madeToOrder: bool, stock: ?int, image: ?string}> */
    public function all(): array;

    /** @return array<string, mixed>|null product, variants (with stock history) and images */
    public function find(int $id): ?array;

    public function create(ProductDraft $draft): int;

    public function update(int $id, ProductDraft $draft): void;

    /** @return int the new variant id */
    public function addVariant(int $productId, string $name, string $sku, int $priceDeltaCents, bool $active): int;

    public function updateVariant(int $variantId, string $name, string $sku, int $priceDeltaCents, bool $active): void;

    public function skuTaken(string $sku, ?int $exceptVariantId = null): bool;

    /** @return array{before: int, after: int} the variant's stock around the adjustment */
    public function adjustStock(int $variantId, int $quantity, string $reason, int $actorId): array;

    public function addImage(int $productId, string $path, string $alt): int;

    public function updateImage(int $imageId, string $alt): void;

    /** @return string|null the file to erase */
    public function deleteImage(int $imageId): ?string;

    public function moveImage(int $imageId, int $direction): void;
}

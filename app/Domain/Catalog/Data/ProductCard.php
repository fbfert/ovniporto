<?php

namespace App\Domain\Catalog\Data;

final readonly class ProductCard
{
    public function __construct(
        public int $id,
        public string $name,
        public string $slug,
        public int $priceCents,
        public ?int $comparePriceCents,
        public ?string $label,
        public bool $madeToOrder,
        public int $productionDays,
        public ?string $imageUrl,
        public ?string $imageAlt,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'priceCents' => $this->priceCents,
            'comparePriceCents' => $this->comparePriceCents,
            'label' => $this->label,
            'madeToOrder' => $this->madeToOrder,
            'productionDays' => $this->productionDays,
            'image' => $this->imageUrl,
            'imageAlt' => $this->imageAlt,
        ];
    }
}

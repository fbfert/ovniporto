<?php

namespace App\Application\Catalog\UseCases;

use App\Domain\Catalog\Contracts\ProductReadRepository;
use App\Domain\Catalog\Data\ProductCard;
use App\Domain\Catalog\Money;

/** /loja/{slug}: the product, up to 3 others for "Combina com" and its schema.org data. */
final readonly class GetProductPage
{
    public const RELATED = 3;

    public function __construct(private ProductReadRepository $catalog) {}

    /** @return array{product: array<string, mixed>, related: list<array<string, mixed>>, structuredData: array<string, mixed>}|null */
    public function execute(string $slug, string $productUrl): ?array
    {
        $product = $this->catalog->findActive($slug);
        if ($product === null) {
            return null;
        }

        return [
            'product' => $product,
            'related' => array_map(fn (ProductCard $card) => $card->toArray(), $this->catalog->related($slug, self::RELATED)),
            'structuredData' => $this->structuredData($product, $productUrl),
        ];
    }

    /**
     * @param  array<string, mixed>  $product
     * @return array<string, mixed>
     */
    private function structuredData(array $product, string $url): array
    {
        /** @var list<array{max: int}> $variants */
        $variants = $product['variants'];
        $inStock = array_filter($variants, fn (array $v) => $v['max'] > 0) !== [];
        /** @var list<array{url: string}> $images */
        $images = $product['images'];

        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $product['name'],
            'description' => $product['shortDescription'] ?? $product['description'],
            'image' => array_column($images, 'url') ?: null,
            'brand' => ['@type' => 'Brand', 'name' => 'OVNIPORTO'],
            'offers' => [
                '@type' => 'Offer',
                'url' => $url,
                'priceCurrency' => 'BRL',
                'price' => Money::cents((int) $product['priceCents'])->decimal(),
                'availability' => $inStock ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
            ],
        ], fn ($value) => $value !== null);
    }
}

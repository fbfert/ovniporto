<?php

namespace App\Application\Shipping\UseCases;

use App\Domain\Catalog\Contracts\ProductReadRepository;
use App\Domain\Shipping\Cep;
use App\Domain\Shipping\Contracts\ShippingProvider;
use Illuminate\Contracts\Cache\Repository as Cache;
use InvalidArgumentException;

/**
 * "Calcular frete" on the product page. The CEP is checked before anything
 * else: an invalid one never reaches the provider. Quotes are cached for a
 * few minutes per CEP and weight.
 */
final readonly class QuoteShipping
{
    public const CACHE_SECONDS = 600;

    public function __construct(
        private ShippingProvider $provider,
        private ProductReadRepository $catalog,
        private Cache $cache,
    ) {}

    /** @return array{cep: string, simulated: bool, options: list<array{id: string, carrier: string, service: string, priceCents: int, days: int}>} */
    public function forVariant(string $cep, int $variantId, int $quantity): array
    {
        $to = Cep::from($cep);
        $variant = $this->catalog->variant($variantId);
        if ($variant === null || ! $variant->available) {
            throw new InvalidArgumentException('Produto indisponível.');
        }
        $quantity = max(1, $quantity);
        $weight = $variant->weightGrams * $quantity;
        $value = $variant->unitPrice->times($quantity)->cents;

        $options = $this->cache->remember(
            "shipping:quote:{$to->digits}:{$weight}:{$value}",
            self::CACHE_SECONDS,
            fn () => array_map(fn ($o) => $o->toArray(), $this->provider->quote($to, $weight, $value)),
        );

        return ['cep' => $to->formatted(), 'simulated' => $this->provider->isSimulated(), 'options' => $options];
    }
}

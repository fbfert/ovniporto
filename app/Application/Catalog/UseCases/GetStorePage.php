<?php

namespace App\Application\Catalog\UseCases;

use App\Domain\Campaign\Contracts\CampaignRepository;
use App\Domain\Catalog\Contracts\ProductReadRepository;
use App\Domain\Catalog\Data\ProductCard;

/** /loja: active products only, and the runway block only when the store share is set. */
final readonly class GetStorePage
{
    public function __construct(
        private ProductReadRepository $catalog,
        private CampaignRepository $campaign,
    ) {}

    /** @return array{products: list<array<string, mixed>>, storeSharePercent: ?float} */
    public function execute(): array
    {
        return [
            'products' => array_map(fn (ProductCard $card) => $card->toArray(), $this->catalog->active()),
            'storeSharePercent' => $this->campaign->settings()->storeSharePercent,
        ];
    }
}

<?php

namespace App\Domain\Shipping\Data;

final readonly class ShippingOption
{
    public function __construct(
        public string $id,
        public string $carrier,
        public string $service,
        public int $priceCents,
        public int $days,
    ) {}

    /** @return array{id: string, carrier: string, service: string, priceCents: int, days: int} */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'carrier' => $this->carrier,
            'service' => $this->service,
            'priceCents' => $this->priceCents,
            'days' => $this->days,
        ];
    }
}

<?php

namespace App\Domain\Orders\Data;

use App\Domain\Orders\OrderTotals;
use App\Domain\Shipping\Data\ShippingOption;

/** Everything a placed order keeps: who, where, how, and copies of what was bought at what price. */
final readonly class NewOrder
{
    /**
     * @param  array{cep: string, street: string, number: string, complement: ?string, district: string, city: string, state: string}|null  $address  null for pickup
     * @param  list<array{variantId: int, productName: string, variantName: string, unitPriceCents: int, quantity: int, madeToOrder: bool, productionDays: int, weightGrams: int}>  $items
     */
    public function __construct(
        public ?int $memberId,
        public ?string $cartToken,
        public string $name,
        public string $email,
        public string $phone,
        public string $cpf,
        public ?array $address,
        public ?ShippingOption $shipping,
        public array $items,
        public OrderTotals $totals,
    ) {}

    public function isPickup(): bool
    {
        return $this->shipping === null;
    }
}

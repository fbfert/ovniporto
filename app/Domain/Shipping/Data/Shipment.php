<?php

namespace App\Domain\Shipping\Data;

/** What we send the logistics provider to buy a label: who receives, where, what and how. */
final readonly class Shipment
{
    /**
     * @param  array{cep: string, street: string, number: string, complement: ?string, district: string, city: string, state: string}  $address
     * @param  list<array{name: string, quantity: int, unitPriceCents: int, weightGrams: int}>  $items
     */
    public function __construct(
        public string $orderNumber,
        public string $serviceId,
        public string $recipientName,
        public string $recipientEmail,
        public string $recipientPhone,
        public string $recipientCpf,
        public array $address,
        public array $items,
    ) {}
}

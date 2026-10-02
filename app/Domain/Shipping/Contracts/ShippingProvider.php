<?php

namespace App\Domain\Shipping\Contracts;

use App\Domain\Shipping\Cep;
use App\Domain\Shipping\Data\Shipment;
use App\Domain\Shipping\Data\ShippingOption;

/**
 * Freight: quotes, labels and tracking. Melhor Envio when its token is set;
 * until then a clearly marked simulation that never sells a real label.
 */
interface ShippingProvider
{
    /**
     * Cheapest first. ShippingUnavailable when the provider does not answer.
     *
     * @return list<ShippingOption>
     */
    public function quote(Cep $to, int $weightGrams, int $valueCents): array;

    /**
     * Buys the label for a paid order.
     *
     * @return array{shipmentId: string, trackingCode: ?string, trackingUrl: ?string}
     */
    public function createLabel(Shipment $shipment): array;

    /** True when the provider reports the parcel delivered. */
    public function isDelivered(string $shipmentId): bool;

    /** True while the quotes are a simulation: product page says so, checkout never charges them. */
    public function isSimulated(): bool;
}

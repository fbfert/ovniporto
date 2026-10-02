<?php

namespace Tests\Support;

use App\Domain\Shipping\Cep;
use App\Domain\Shipping\Contracts\ShippingProvider;
use App\Domain\Shipping\Data\Shipment;
use App\Domain\Shipping\Data\ShippingOption;
use App\Domain\Shipping\ShippingUnavailable;

final class FakeShipping implements ShippingProvider
{
    public int $quotes = 0;

    public int $labels = 0;

    public bool $down = false;

    public bool $delivered = false;

    public function quote(Cep $to, int $weightGrams, int $valueCents): array
    {
        $this->quotes++;
        if ($this->down) {
            throw new ShippingUnavailable;
        }

        return [
            new ShippingOption('1', 'Correios', 'PAC', 1250, 8),
            new ShippingOption('2', 'Correios', 'SEDEX', 3190, 3),
        ];
    }

    public function createLabel(Shipment $shipment): array
    {
        $this->labels++;

        return ['shipmentId' => 'ME-1', 'trackingCode' => 'AA123456789BR', 'trackingUrl' => null];
    }

    public function isDelivered(string $shipmentId): bool
    {
        return $this->delivered;
    }

    public function isSimulated(): bool
    {
        return false;
    }
}

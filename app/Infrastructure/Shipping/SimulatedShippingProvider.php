<?php

namespace App\Infrastructure\Shipping;

use App\Domain\Shipping\Cep;
use App\Domain\Shipping\Contracts\ShippingProvider;
use App\Domain\Shipping\Data\Shipment;
use App\Domain\Shipping\Data\ShippingOption;
use App\Domain\Shipping\ShippingUnavailable;

/**
 * Stand-in until Melhor Envio is connected: a rough table by distance from
 * Lages (first CEP digit = region) and weight. The store labels these quotes
 * as a simulation; checkout uses the real provider.
 */
final class SimulatedShippingProvider implements ShippingProvider
{
    /** Region by first CEP digit → [extra cents, extra days]. 8 = PR/SC, home of Lages. */
    private const REGIONS = [
        '8' => [0, 0], '9' => [400, 1], '0' => [900, 2], '1' => [900, 2], '2' => [1100, 3],
        '3' => [1100, 3], '4' => [1500, 4], '5' => [1700, 5], '6' => [2000, 6], '7' => [1500, 4],
    ];

    public function quote(Cep $to, int $weightGrams, int $valueCents): array
    {
        [$extra, $extraDays] = self::REGIONS[$to->digits[0]];
        $weightSteps = intdiv(max(0, $weightGrams - 1), 300);

        return [
            new ShippingOption('simulated-letter', 'Correios', 'Carta registrada', 1200 + intdiv($extra, 2) + $weightSteps * 300, 6 + $extraDays),
            new ShippingOption('simulated-pac', 'Correios', 'PAC', 2100 + $extra + $weightSteps * 400, 7 + $extraDays),
            new ShippingOption('simulated-sedex', 'Correios', 'SEDEX', 3400 + $extra * 2 + $weightSteps * 700, 2 + intdiv($extraDays, 2)),
        ];
    }

    /** A simulation never buys a real label. */
    public function createLabel(Shipment $shipment): array
    {
        throw new ShippingUnavailable('O frete real ainda não está conectado (Melhor Envio).');
    }

    public function isDelivered(string $shipmentId): bool
    {
        return false;
    }

    public function isSimulated(): bool
    {
        return true;
    }
}

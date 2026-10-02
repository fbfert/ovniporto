<?php

namespace App\Domain\Shipping\Contracts;

use App\Domain\Shipping\Cep;
use App\Domain\Shipping\Data\ShippingOption;

/**
 * Freight quotes (and, with add-checkout-payments, labels). Melhor Envio in
 * production; a clearly marked simulation until its credentials exist.
 */
interface ShippingProvider
{
    /** @return list<ShippingOption> cheapest first */
    public function quote(Cep $to, int $weightGrams, int $valueCents): array;

    /** True while the quotes are a simulation: the store says so next to the prices. */
    public function isSimulated(): bool;
}

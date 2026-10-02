<?php

namespace App\Domain\Shipping;

use RuntimeException;

/** The freight provider did not answer (or is not connected yet). Pickup in Lages always remains. */
final class ShippingUnavailable extends RuntimeException
{
    public function __construct(string $reason = 'O cálculo de frete está fora do ar agora. Dá para retirar em Lages ou tentar de novo daqui a pouco.')
    {
        parent::__construct($reason);
    }
}

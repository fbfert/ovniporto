<?php

namespace App\Domain\Shipping\Contracts;

use App\Domain\Shipping\Cep;

/** CEP → street, district, city and state, so the buyer only types number and complement. */
interface AddressLookup
{
    /** @return array{street: string, district: string, city: string, state: string}|null */
    public function find(Cep $cep): ?array;
}

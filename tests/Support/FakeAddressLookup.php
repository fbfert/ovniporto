<?php

namespace Tests\Support;

use App\Domain\Shipping\Cep;
use App\Domain\Shipping\Contracts\AddressLookup;

final class FakeAddressLookup implements AddressLookup
{
    public function find(Cep $cep): ?array
    {
        return ['street' => 'Rua Coronel Córdova', 'district' => 'Centro', 'city' => 'Lages', 'state' => 'SC'];
    }
}

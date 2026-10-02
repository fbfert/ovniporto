<?php

namespace App\Infrastructure\Shipping;

use App\Domain\Shipping\Cep;
use App\Domain\Shipping\Contracts\AddressLookup;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

/** ViaCEP (free, no key). Answers are cached for a day; a miss just leaves the fields for the buyer. */
final readonly class ViaCepAddressLookup implements AddressLookup
{
    public function __construct(private Cache $cache) {}

    public function find(Cep $cep): ?array
    {
        $cached = $this->cache->get("viacep:{$cep->digits}");
        if (is_array($cached)) {
            /** @var array{street: string, district: string, city: string, state: string} $cached */
            return $cached;
        }

        try {
            $response = Http::timeout(5)->acceptJson()->get("https://viacep.com.br/ws/{$cep->digits}/json/");
        } catch (ConnectionException) {
            return null;
        }
        $body = $response->json();
        if (! $response->successful() || ! is_array($body) || isset($body['erro'])) {
            return null;
        }

        $address = [
            'street' => (string) ($body['logradouro'] ?? ''),
            'district' => (string) ($body['bairro'] ?? ''),
            'city' => (string) ($body['localidade'] ?? ''),
            'state' => (string) ($body['uf'] ?? ''),
        ];
        $this->cache->put("viacep:{$cep->digits}", $address, 86_400);

        return $address;
    }
}

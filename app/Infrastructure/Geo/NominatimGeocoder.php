<?php

namespace App\Infrastructure\Geo;

use App\Domain\Map\Contracts\Geocoder;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

/**
 * Reverse geocoding on OpenStreetMap's Nominatim. Its usage policy asks for an
 * identifying User-Agent, at most one request per second (the LocateSighting
 * job is rate limited) and caching (CachedGeocoder wraps this class).
 * Data © OpenStreetMap contributors, shown next to the city in the panel.
 */
final class NominatimGeocoder implements Geocoder
{
    private const URL = 'https://nominatim.openstreetmap.org/reverse';

    private const TOWN_KEYS = ['city', 'town', 'village', 'municipality', 'county'];

    public function cityAt(float $lat, float $lng): ?string
    {
        try {
            $response = Http::timeout(5)
                ->withHeaders(['User-Agent' => 'OVNIPORTO/1.0 (https://ovniporto.tars.art.br)'])
                ->get(self::URL, [
                    'format' => 'jsonv2',
                    'lat' => $lat,
                    'lon' => $lng,
                    'zoom' => 10,
                    'accept-language' => 'pt-BR',
                ]);
        } catch (ConnectionException) {
            return null;
        }
        if (! $response->successful()) {
            return null;
        }

        /** @var array<string, string> $address */
        $address = (array) $response->json('address', []);
        $town = collect(self::TOWN_KEYS)->map(fn (string $key) => $address[$key] ?? null)->first(fn (?string $v) => $v !== null);
        if ($town === null) {
            return null;
        }
        $state = $address['ISO3166-2-lvl4'] ?? null;

        return $state !== null && str_starts_with($state, 'BR-') ? "{$town}, ".substr($state, 3) : $town;
    }
}

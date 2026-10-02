<?php

namespace App\Infrastructure\Geo;

use App\Domain\Map\Contracts\Geocoder;
use Illuminate\Contracts\Cache\Repository;

/**
 * Remembers answers per point rounded to ~1 km, so nearby reports never hit
 * the service twice. A miss is remembered for a while too, to stay polite.
 */
final readonly class CachedGeocoder implements Geocoder
{
    private const MISS = '';

    private const MISS_TTL_SECONDS = 86_400;

    public function __construct(
        private Geocoder $inner,
        private Repository $cache,
    ) {}

    public function cityAt(float $lat, float $lng): ?string
    {
        $key = sprintf('geocoder:city:%.2f:%.2f', $lat, $lng);
        $cached = $this->cache->get($key);
        if (is_string($cached)) {
            return $cached === self::MISS ? null : $cached;
        }

        $city = $this->inner->cityAt($lat, $lng);
        $city === null
            ? $this->cache->put($key, self::MISS, self::MISS_TTL_SECONDS)
            : $this->cache->forever($key, $city);

        return $city;
    }

    /** Addresses are looked up once a day at most; the operator confirms the point on the map anyway. */
    public function locate(string $address): ?array
    {
        $key = 'geocoder:address:'.sha1(mb_strtolower(trim($address)));
        $cached = $this->cache->get($key);
        if (is_array($cached) && isset($cached['lat'], $cached['lng'])) {
            return ['lat' => (float) $cached['lat'], 'lng' => (float) $cached['lng']];
        }

        $point = $this->inner->locate($address);
        if ($point !== null) {
            $this->cache->put($key, $point, self::MISS_TTL_SECONDS);
        }

        return $point;
    }
}

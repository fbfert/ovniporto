<?php

namespace App\Infrastructure\Sightings;

use Closure;
use Illuminate\Support\Facades\Cache;

/**
 * 60 s cache for the public Livro, keyed by a version number. Any change to a
 * report (approval, unpublishing, deletion) bumps the version, so every cached
 * combination of filters goes stale at once; the TTL is only a safety net.
 */
final class PublicSightingsCache
{
    public const TTL_SECONDS = 60;

    private const VERSION_KEY = 'sightings:public:version';

    /**
     * @template T
     *
     * @param  Closure(): T  $compute
     * @return T
     */
    public static function remember(string $key, Closure $compute): mixed
    {
        $version = (int) Cache::get(self::VERSION_KEY, 1);

        return Cache::remember("sightings:public:v{$version}:{$key}", self::TTL_SECONDS, $compute);
    }

    public static function bump(): void
    {
        Cache::add(self::VERSION_KEY, 1);
        Cache::increment(self::VERSION_KEY);
    }
}

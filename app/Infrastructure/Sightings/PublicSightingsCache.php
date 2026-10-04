<?php

namespace App\Infrastructure\Sightings;

use App\Infrastructure\Cache\FragmentCache;
use Closure;

/**
 * 60 s cache for the public Livro. Any change to a report (approval, unpublishing,
 * deletion) bumps the fragment, so every cached combination of filters goes stale
 * at once; the TTL is only a safety net.
 */
final class PublicSightingsCache
{
    public const TTL_SECONDS = 60;

    public const FRAGMENT = 'sightings';

    /**
     * @template T
     *
     * @param  Closure(): T  $compute
     * @return T
     */
    public static function remember(string $key, Closure $compute): mixed
    {
        return FragmentCache::remember(self::FRAGMENT, $key, self::TTL_SECONDS, $compute);
    }

    public static function bump(): void
    {
        FragmentCache::bump(self::FRAGMENT);
    }
}

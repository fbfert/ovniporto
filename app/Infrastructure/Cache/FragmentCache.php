<?php

namespace App\Infrastructure\Cache;

use Closure;
use Illuminate\Support\Facades\Cache;

/**
 * Cached pieces of public pages, grouped by fragment ("home", "sightings"). Each
 * fragment has a version; bumping it (from model events) invalidates every entry
 * at once without knowing their keys. The TTL is only a safety net.
 */
final class FragmentCache
{
    /**
     * @template T
     *
     * @param  Closure(): T  $compute
     * @return T
     */
    public static function remember(string $fragment, string $key, int $ttlSeconds, Closure $compute): mixed
    {
        $version = (int) Cache::get(self::versionKey($fragment), 1);

        return Cache::remember("fragment:{$fragment}:v{$version}:{$key}", $ttlSeconds, $compute);
    }

    public static function bump(string ...$fragments): void
    {
        foreach ($fragments as $fragment) {
            Cache::add(self::versionKey($fragment), 1);
            Cache::increment(self::versionKey($fragment));
        }
    }

    private static function versionKey(string $fragment): string
    {
        return "fragment:{$fragment}:version";
    }
}

<?php

namespace App\Observers;

use App\Application\Content\UseCases\GetHomeData;
use App\Infrastructure\Cache\FragmentCache;

/**
 * Registered on every model the home shows (AppServiceProvider): any save or delete
 * makes the cached home stale at once.
 */
class HomeFragmentObserver
{
    public function saved(): void
    {
        FragmentCache::bump(GetHomeData::FRAGMENT);
    }

    public function deleted(): void
    {
        FragmentCache::bump(GetHomeData::FRAGMENT);
    }
}

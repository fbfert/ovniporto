<?php

namespace App\Observers;

use App\Infrastructure\Sightings\PublicSightingsCache;

/** Whatever changes a report, the public Livro is recomputed on the next request. */
class SightingObserver
{
    public function saved(): void
    {
        PublicSightingsCache::bump();
    }

    public function deleted(): void
    {
        PublicSightingsCache::bump();
    }
}

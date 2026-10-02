<?php

namespace App\Providers;

use App\Domain\Panel\PanelArea;
use App\Models\Member;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Routes say `panel:relatos` (EnsurePanelArea); PanelArea decides which roles get in.
        Gate::define('panel', fn (Member $member, string $area) => PanelArea::tryFrom($area)?->allows($member->role) ?? false);

        // Nominatim's usage policy: one request per second, at most.
        RateLimiter::for('geocoder', fn () => Limit::perSecond(1));
    }
}

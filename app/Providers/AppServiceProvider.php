<?php

namespace App\Providers;

use App\Domain\Panel\PanelArea;
use App\Infrastructure\Monitoring\JobFailureAlert;
use App\Models\ContentBlock;
use App\Models\Member;
use App\Models\PlaceSpace;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\RegionPartner;
use App\Models\Sighting;
use App\Models\SightingPhoto;
use App\Observers\HomeFragmentObserver;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Vite;
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

        // Pages arrive rendered by SSR: on 4G the JS module preloads (~200 kB, high priority) only
        // compete with the CSS and fonts the first paint needs. Without them the JS loads right after,
        // one round trip later, while the page is already readable.
        Vite::usePreloadTagAttributes(fn (string $src, string $url) => str_ends_with($url, '.js') ? false : []);
        if (filled(config('ovniporto.vite_hot_file'))) {
            Vite::useHotFile((string) config('ovniporto.vite_hot_file'));
        }

        // A job that fails for good e-mails the operators (Horizon covers queues that wait too long).
        Event::listen(JobFailed::class, JobFailureAlert::class);

        // Everything the home shows: a change to any of these makes the cached home stale.
        foreach (self::HOME_MODELS as $model) {
            $model::observe(HomeFragmentObserver::class);
        }
    }

    /** @var list<class-string<Model>> */
    private const HOME_MODELS = [
        Member::class, Sighting::class, SightingPhoto::class, Product::class, ProductImage::class,
        ProductVariant::class, PlaceSpace::class, RegionPartner::class, ContentBlock::class,
    ];
}

<?php

namespace App\Providers;

use App\Domain\Panel\PanelArea;
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

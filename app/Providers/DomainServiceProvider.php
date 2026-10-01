<?php

namespace App\Providers;

use App\Domain\Campaign\Contracts\WaitlistNotifier;
use App\Domain\Campaign\Contracts\WaitlistRepository;
use App\Domain\Catalog\Contracts\ProductReadRepository;
use App\Domain\Content\Contracts\ContentBlockRepository;
use App\Domain\Members\Contracts\MemberRepository;
use App\Domain\Place\Contracts\PlaceSpaceRepository;
use App\Domain\Region\Contracts\RegionPartnerRepository;
use App\Domain\Sightings\Contracts\SightingReadRepository;
use App\Infrastructure\Mail\MailWaitlistNotifier;
use App\Infrastructure\Persistence\Eloquent\EloquentContentBlockRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentMemberRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentPlaceSpaceRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentProductReadRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentRegionPartnerRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentSightingReadRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentWaitlistRepository;
use Illuminate\Support\ServiceProvider;

/**
 * Binds every Domain contract to its Infrastructure implementation.
 * Swapping an integration means changing one line here.
 */
class DomainServiceProvider extends ServiceProvider
{
    /** @var array<class-string, class-string> */
    public array $bindings = [
        ContentBlockRepository::class => EloquentContentBlockRepository::class,
        MemberRepository::class => EloquentMemberRepository::class,
        SightingReadRepository::class => EloquentSightingReadRepository::class,
        ProductReadRepository::class => EloquentProductReadRepository::class,
        PlaceSpaceRepository::class => EloquentPlaceSpaceRepository::class,
        RegionPartnerRepository::class => EloquentRegionPartnerRepository::class,
        WaitlistRepository::class => EloquentWaitlistRepository::class,
        WaitlistNotifier::class => MailWaitlistNotifier::class,
    ];
}

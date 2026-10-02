<?php

namespace App\Providers;

use App\Application\Members\UseCases\BuildMemberExport;
use App\Application\Members\UseCases\DeleteAccount;
use App\Domain\Audit\Contracts\Auditor;
use App\Domain\Campaign\Contracts\CampaignRepository;
use App\Domain\Campaign\Contracts\WaitlistNotifier;
use App\Domain\Campaign\Contracts\WaitlistRepository;
use App\Domain\Catalog\Contracts\ProductReadRepository;
use App\Domain\Content\Contracts\ContentBlockRepository;
use App\Domain\Content\Contracts\EditorialListRepository;
use App\Domain\Content\Contracts\MarkdownRenderer;
use App\Domain\Map\Contracts\Geocoder;
use App\Domain\Members\Contracts\IdentityProvider;
use App\Domain\Members\Contracts\MemberRepository;
use App\Domain\Place\Contracts\ConstructionPostRepository;
use App\Domain\Place\Contracts\PlaceSpaceRepository;
use App\Domain\Place\Contracts\SitePhotoRepository;
use App\Domain\Region\Contracts\RegionPartnerRepository;
use App\Domain\Sightings\Contracts\ImageProcessor;
use App\Domain\Sightings\Contracts\MemberSightingRepository;
use App\Domain\Sightings\Contracts\ModerationRepository;
use App\Domain\Sightings\Contracts\PhotoStorage;
use App\Domain\Sightings\Contracts\SightingNotifier;
use App\Domain\Sightings\Contracts\SightingReadRepository;
use App\Domain\Sightings\Contracts\SightingWriteRepository;
use App\Infrastructure\Audit\DatabaseAuditor;
use App\Infrastructure\Content\CommonMarkRenderer;
use App\Infrastructure\Geo\CachedGeocoder;
use App\Infrastructure\Geo\NominatimGeocoder;
use App\Infrastructure\Identity\GoogleIdentityProvider;
use App\Infrastructure\Images\GdImageProcessor;
use App\Infrastructure\Images\PrivatePhotoStorage;
use App\Infrastructure\Mail\MailSightingNotifier;
use App\Infrastructure\Mail\MailWaitlistNotifier;
use App\Infrastructure\Members\SightingsContentEraser;
use App\Infrastructure\Members\SightingsDataSource;
use App\Infrastructure\Members\WaitlistDataSource;
use App\Infrastructure\Persistence\Eloquent\EloquentCampaignRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentConstructionPostRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentContentBlockRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentEditorialListRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentMemberRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentMemberSightingRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentModerationRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentPlaceSpaceRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentProductReadRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentRegionPartnerRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentSightingReadRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentSightingWriteRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentSitePhotoRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentWaitlistRepository;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Support\ServiceProvider;

/**
 * Binds every Domain contract to its Infrastructure implementation.
 * Swapping an integration means changing one line here.
 */
class DomainServiceProvider extends ServiceProvider
{
    /**
     * Modules that erase their part when a member deletes the account.
     * Orders joins with add-checkout-payments (it anonymizes instead of deleting).
     */
    private const MEMBER_ERASERS = [SightingsContentEraser::class];

    /** Modules that contribute a section to "Baixar meus dados". */
    private const MEMBER_DATA_SOURCES = [SightingsDataSource::class, WaitlistDataSource::class];

    /** @var array<class-string, class-string> */
    public array $bindings = [
        ContentBlockRepository::class => EloquentContentBlockRepository::class,
        EditorialListRepository::class => EloquentEditorialListRepository::class,
        MarkdownRenderer::class => CommonMarkRenderer::class,
        MemberRepository::class => EloquentMemberRepository::class,
        IdentityProvider::class => GoogleIdentityProvider::class,
        SightingReadRepository::class => EloquentSightingReadRepository::class,
        MemberSightingRepository::class => EloquentMemberSightingRepository::class,
        SightingWriteRepository::class => EloquentSightingWriteRepository::class,
        ImageProcessor::class => GdImageProcessor::class,
        PhotoStorage::class => PrivatePhotoStorage::class,
        SightingNotifier::class => MailSightingNotifier::class,
        ModerationRepository::class => EloquentModerationRepository::class,
        Auditor::class => DatabaseAuditor::class,
        ProductReadRepository::class => EloquentProductReadRepository::class,
        PlaceSpaceRepository::class => EloquentPlaceSpaceRepository::class,
        SitePhotoRepository::class => EloquentSitePhotoRepository::class,
        ConstructionPostRepository::class => EloquentConstructionPostRepository::class,
        CampaignRepository::class => EloquentCampaignRepository::class,
        RegionPartnerRepository::class => EloquentRegionPartnerRepository::class,
        WaitlistRepository::class => EloquentWaitlistRepository::class,
        WaitlistNotifier::class => MailWaitlistNotifier::class,
    ];

    public function register(): void
    {
        $this->app->tag(self::MEMBER_ERASERS, 'member.erasers');
        $this->app->tag(self::MEMBER_DATA_SOURCES, 'member.data-sources');

        $this->app->when(DeleteAccount::class)->needs('$erasers')->giveTagged('member.erasers');
        $this->app->when(BuildMemberExport::class)->needs('$sources')->giveTagged('member.data-sources');

        $this->app->bind(Geocoder::class, fn () => new CachedGeocoder(new NominatimGeocoder, $this->app->make(Cache::class)));
    }
}

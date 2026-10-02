<?php

namespace App\Providers;

use App\Application\Members\UseCases\BuildMemberExport;
use App\Application\Members\UseCases\DeleteAccount;
use App\Application\Place\UseCases\ManagePlace;
use App\Domain\Audit\Contracts\Auditor;
use App\Domain\Campaign\Contracts\CampaignAdminRepository;
use App\Domain\Campaign\Contracts\CampaignRepository;
use App\Domain\Campaign\Contracts\WaitlistNotifier;
use App\Domain\Campaign\Contracts\WaitlistRepository;
use App\Domain\Catalog\Contracts\ProductReadRepository;
use App\Domain\Content\Contracts\ContentAdminRepository;
use App\Domain\Content\Contracts\ContentBlockRepository;
use App\Domain\Content\Contracts\EditorialListRepository;
use App\Domain\Content\Contracts\ImageLibrary;
use App\Domain\Content\Contracts\MarkdownRenderer;
use App\Domain\Map\Contracts\Geocoder;
use App\Domain\Members\Contracts\IdentityProvider;
use App\Domain\Members\Contracts\MemberAdminRepository;
use App\Domain\Members\Contracts\MemberRepository;
use App\Domain\Orders\Contracts\CartRepository;
use App\Domain\Place\Contracts\ConstructionPostRepository;
use App\Domain\Place\Contracts\DiaryAdminRepository;
use App\Domain\Place\Contracts\PlaceAdminRepository;
use App\Domain\Place\Contracts\PlaceSpaceRepository;
use App\Domain\Place\Contracts\SitePhotoRepository;
use App\Domain\Region\Contracts\ConsentProofStorage;
use App\Domain\Region\Contracts\RegionAdminRepository;
use App\Domain\Region\Contracts\RegionPartnerRepository;
use App\Domain\Shipping\Contracts\ShippingProvider;
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
use App\Infrastructure\Images\PublicImageLibrary;
use App\Infrastructure\Mail\MailSightingNotifier;
use App\Infrastructure\Mail\MailWaitlistNotifier;
use App\Infrastructure\Members\SightingsContentEraser;
use App\Infrastructure\Members\SightingsDataSource;
use App\Infrastructure\Members\WaitlistDataSource;
use App\Infrastructure\Persistence\Eloquent\EloquentCampaignAdminRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentCampaignRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentCartRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentConstructionPostRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentContentAdminRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentContentBlockRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentDiaryAdminRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentEditorialListRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentMemberAdminRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentMemberRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentMemberSightingRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentModerationRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentPlaceAdminRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentPlaceSpaceRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentProductReadRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentRegionAdminRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentRegionPartnerRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentSightingReadRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentSightingWriteRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentSitePhotoRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentWaitlistRepository;
use App\Infrastructure\Region\PrivateConsentProofStorage;
use App\Infrastructure\Shipping\SimulatedShippingProvider;
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
        MemberAdminRepository::class => EloquentMemberAdminRepository::class,
        IdentityProvider::class => GoogleIdentityProvider::class,
        SightingReadRepository::class => EloquentSightingReadRepository::class,
        MemberSightingRepository::class => EloquentMemberSightingRepository::class,
        SightingWriteRepository::class => EloquentSightingWriteRepository::class,
        ImageProcessor::class => GdImageProcessor::class,
        PhotoStorage::class => PrivatePhotoStorage::class,
        SightingNotifier::class => MailSightingNotifier::class,
        ModerationRepository::class => EloquentModerationRepository::class,
        Auditor::class => DatabaseAuditor::class,
        CartRepository::class => EloquentCartRepository::class,
        ShippingProvider::class => SimulatedShippingProvider::class,
        ImageLibrary::class => PublicImageLibrary::class,
        ContentAdminRepository::class => EloquentContentAdminRepository::class,
        CampaignAdminRepository::class => EloquentCampaignAdminRepository::class,
        PlaceAdminRepository::class => EloquentPlaceAdminRepository::class,
        DiaryAdminRepository::class => EloquentDiaryAdminRepository::class,
        RegionAdminRepository::class => EloquentRegionAdminRepository::class,
        ConsentProofStorage::class => PrivateConsentProofStorage::class,
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

        $this->app->when(ManagePlace::class)->needs('$embedHosts')->give(fn () => (array) config('ovniporto.embed_hosts'));
        $this->app->bind(Geocoder::class, fn () => new CachedGeocoder(new NominatimGeocoder, $this->app->make(Cache::class)));
    }
}

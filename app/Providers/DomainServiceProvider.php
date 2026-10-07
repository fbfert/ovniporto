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
use App\Domain\Catalog\Contracts\ProductAdminRepository;
use App\Domain\Catalog\Contracts\ProductReadRepository;
use App\Domain\Content\Contracts\ContentAdminRepository;
use App\Domain\Content\Contracts\ContentBlockRepository;
use App\Domain\Content\Contracts\EditorialListRepository;
use App\Domain\Content\Contracts\ImageLibrary;
use App\Domain\Content\Contracts\MarkdownRenderer;
use App\Domain\Content\Contracts\OgCardRepository;
use App\Domain\Content\Contracts\OgImageRenderer;
use App\Domain\Content\Contracts\PublishedContentIndex;
use App\Domain\Manual\Contracts\ManualLibrary;
use App\Domain\Map\Contracts\Geocoder;
use App\Domain\Members\Contracts\IdentityProvider;
use App\Domain\Members\Contracts\MemberAdminRepository;
use App\Domain\Members\Contracts\MemberRepository;
use App\Domain\Orders\Contracts\CartRepository;
use App\Domain\Orders\Contracts\OrderAdminRepository;
use App\Domain\Orders\Contracts\OrderNotifier;
use App\Domain\Orders\Contracts\OrderRepository;
use App\Domain\Orders\Contracts\ProductionDocument;
use App\Domain\Origin\Contracts\CollaboratorNotifier;
use App\Domain\Origin\Contracts\CollaboratorRepository;
use App\Domain\Origin\Contracts\OriginLibrary;
use App\Domain\Panel\Contracts\DashboardRepository;
use App\Domain\Payments\Contracts\PaymentGateway;
use App\Domain\Place\Contracts\ConstructionPostRepository;
use App\Domain\Place\Contracts\DiaryAdminRepository;
use App\Domain\Place\Contracts\PlaceAdminRepository;
use App\Domain\Place\Contracts\PlaceSpaceRepository;
use App\Domain\Place\Contracts\SitePhotoRepository;
use App\Domain\Privacy\Contracts\ConsentLedger;
use App\Domain\Privacy\Contracts\PrivacyPractices;
use App\Domain\Region\Contracts\ConsentProofStorage;
use App\Domain\Region\Contracts\RegionAdminRepository;
use App\Domain\Region\Contracts\RegionPartnerRepository;
use App\Domain\Settings\Contracts\IntegrationDirectory;
use App\Domain\Settings\Contracts\MailTester;
use App\Domain\Settings\Contracts\OperationalSettingsRepository;
use App\Domain\Settings\Contracts\SettingsBaseline;
use App\Domain\Shipping\Contracts\AddressLookup;
use App\Domain\Shipping\Contracts\ShippingProvider;
use App\Domain\Sightings\Contracts\HistoricalCaseLibrary;
use App\Domain\Sightings\Contracts\ImageProcessor;
use App\Domain\Sightings\Contracts\MemberSightingRepository;
use App\Domain\Sightings\Contracts\ModerationRepository;
use App\Domain\Sightings\Contracts\PhotoStorage;
use App\Domain\Sightings\Contracts\SightingNotifier;
use App\Domain\Sightings\Contracts\SightingReadRepository;
use App\Domain\Sightings\Contracts\SightingWriteRepository;
use App\Infrastructure\Audit\DatabaseAuditor;
use App\Infrastructure\Brand\GdOgImageRenderer;
use App\Infrastructure\Content\CommonMarkRenderer;
use App\Infrastructure\Documents\DompdfProductionDocument;
use App\Infrastructure\Geo\CachedGeocoder;
use App\Infrastructure\Geo\NominatimGeocoder;
use App\Infrastructure\Geo\OfflineGeocoder;
use App\Infrastructure\Identity\GoogleIdentityProvider;
use App\Infrastructure\Images\GdImageProcessor;
use App\Infrastructure\Images\PrivatePhotoStorage;
use App\Infrastructure\Images\PublicImageLibrary;
use App\Infrastructure\Mail\MailCollaboratorNotifier;
use App\Infrastructure\Mail\MailOrderNotifier;
use App\Infrastructure\Mail\MailSightingNotifier;
use App\Infrastructure\Mail\MailWaitlistNotifier;
use App\Infrastructure\Manual\JsonManualLibrary;
use App\Infrastructure\Members\CollaboratorContentEraser;
use App\Infrastructure\Members\CollaboratorDataSource;
use App\Infrastructure\Members\ConsentsContentEraser;
use App\Infrastructure\Members\ConsentsDataSource;
use App\Infrastructure\Members\OrdersContentEraser;
use App\Infrastructure\Members\OrdersDataSource;
use App\Infrastructure\Members\SightingsContentEraser;
use App\Infrastructure\Members\SightingsDataSource;
use App\Infrastructure\Members\WaitlistContentEraser;
use App\Infrastructure\Members\WaitlistDataSource;
use App\Infrastructure\Origin\JsonOriginLibrary;
use App\Infrastructure\Payments\PayPalGateway;
use App\Infrastructure\Payments\SimulatedPaymentGateway;
use App\Infrastructure\Payments\UnconfiguredPaymentGateway;
use App\Infrastructure\Persistence\Eloquent\EloquentCampaignAdminRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentCampaignRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentCartRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentCollaboratorRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentConstructionPostRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentContentAdminRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentContentBlockRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentDashboardRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentDiaryAdminRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentEditorialListRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentMemberAdminRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentMemberRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentMemberSightingRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentModerationRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentOgCardRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentOrderAdminRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentOrderRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentPlaceAdminRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentPlaceSpaceRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentProductAdminRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentProductReadRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentPublishedContentIndex;
use App\Infrastructure\Persistence\Eloquent\EloquentRegionAdminRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentRegionPartnerRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentSightingReadRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentSightingWriteRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentSitePhotoRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentWaitlistRepository;
use App\Infrastructure\Privacy\CodePrivacyPractices;
use App\Infrastructure\Privacy\DatabaseConsentLedger;
use App\Infrastructure\Region\PrivateConsentProofStorage;
use App\Infrastructure\Settings\ConfigIntegrationDirectory;
use App\Infrastructure\Settings\EloquentOperationalSettingsRepository;
use App\Infrastructure\Settings\OperationalSettingsApplier;
use App\Infrastructure\Settings\SmtpMailTester;
use App\Infrastructure\Shipping\MelhorEnvioShippingProvider;
use App\Infrastructure\Shipping\SimulatedShippingProvider;
use App\Infrastructure\Shipping\ViaCepAddressLookup;
use App\Infrastructure\Sightings\JsonHistoricalCaseLibrary;
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
     * Orders are anonymized instead of deleted (tax law keeps them).
     */
    private const MEMBER_ERASERS = [SightingsContentEraser::class, OrdersContentEraser::class, WaitlistContentEraser::class, CollaboratorContentEraser::class, ConsentsContentEraser::class];

    /** Modules that contribute a section to "Baixar meus dados". */
    private const MEMBER_DATA_SOURCES = [SightingsDataSource::class, OrdersDataSource::class, WaitlistDataSource::class, CollaboratorDataSource::class, ConsentsDataSource::class];

    /** @var array<class-string, class-string> */
    public array $bindings = [
        ContentBlockRepository::class => EloquentContentBlockRepository::class,
        EditorialListRepository::class => EloquentEditorialListRepository::class,
        MarkdownRenderer::class => CommonMarkRenderer::class,
        ConsentLedger::class => DatabaseConsentLedger::class,
        PrivacyPractices::class => CodePrivacyPractices::class,
        OgCardRepository::class => EloquentOgCardRepository::class,
        OgImageRenderer::class => GdOgImageRenderer::class,
        PublishedContentIndex::class => EloquentPublishedContentIndex::class,
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
        AddressLookup::class => ViaCepAddressLookup::class,
        OrderRepository::class => EloquentOrderRepository::class,
        OrderAdminRepository::class => EloquentOrderAdminRepository::class,
        ProductAdminRepository::class => EloquentProductAdminRepository::class,
        DashboardRepository::class => EloquentDashboardRepository::class,
        ProductionDocument::class => DompdfProductionDocument::class,
        OrderNotifier::class => MailOrderNotifier::class,
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
        CollaboratorRepository::class => EloquentCollaboratorRepository::class,
        OperationalSettingsRepository::class => EloquentOperationalSettingsRepository::class,
        MailTester::class => SmtpMailTester::class,
        CollaboratorNotifier::class => MailCollaboratorNotifier::class,
    ];

    public function register(): void
    {
        $this->app->tag(self::MEMBER_ERASERS, 'member.erasers');
        $this->app->tag(self::MEMBER_DATA_SOURCES, 'member.data-sources');

        $this->app->when(DeleteAccount::class)->needs('$erasers')->giveTagged('member.erasers');
        $this->app->when(BuildMemberExport::class)->needs('$sources')->giveTagged('member.data-sources');

        $this->app->singleton(ShippingProvider::class, fn () => $this->shippingProvider());
        $this->app->singleton(OperationalSettingsApplier::class);
        $this->app->alias(OperationalSettingsApplier::class, SettingsBaseline::class);
        $this->app->bind(IntegrationDirectory::class, fn () => new ConfigIntegrationDirectory($this->app->make('config'), $this->app->environment('production')));
        $this->app->singleton(OriginLibrary::class, fn () => new JsonOriginLibrary(resource_path('content/origin')));
        $this->app->singleton(ManualLibrary::class, fn () => new JsonManualLibrary(resource_path('content/manual'), $this->app->make(MarkdownRenderer::class)));
        $this->app->singleton(HistoricalCaseLibrary::class, fn () => new JsonHistoricalCaseLibrary(resource_path('content/sightings/historical-cases.json')));
        $this->app->singleton(PaymentGateway::class, fn () => $this->paymentGateway());

        $this->app->when(ManagePlace::class)->needs('$embedHosts')->give(fn () => (array) config('ovniporto.embed_hosts'));
        $this->app->bind(Geocoder::class, fn () => config('ovniporto.geocoder') === 'offline'
            ? new OfflineGeocoder
            : new CachedGeocoder(new NominatimGeocoder, $this->app->make(Cache::class)));
    }

    /** Melhor Envio once its token is set; until then, the marked simulation. */
    private function shippingProvider(): ShippingProvider
    {
        $config = (array) config('services.melhor_envio');
        if (blank($config['token'] ?? null)) {
            return new SimulatedShippingProvider;
        }

        return new MelhorEnvioShippingProvider(
            token: (string) $config['token'],
            mode: (string) ($config['env'] ?? 'sandbox'),
            fromCep: (string) ($config['from_postal_code'] ?? ''),
            package: (array) config('ovniporto.shipping.package'),
            sender: (array) config('ovniporto.shipping.sender'),
            userAgent: (string) ($config['user_agent'] ?? 'OVNIPORTO'),
        );
    }

    /** PayPal once its credentials are set; a simulation only outside production; otherwise nobody pays. */
    private function paymentGateway(): PaymentGateway
    {
        $config = (array) config('services.paypal');
        if (filled($config['client_id'] ?? null) && filled($config['client_secret'] ?? null)) {
            return new PayPalGateway(
                clientId: (string) $config['client_id'],
                secret: (string) $config['client_secret'],
                mode: (string) ($config['mode'] ?? 'sandbox'),
                webhookId: $config['webhook_id'] ?? null,
                cache: $this->app->make(Cache::class),
            );
        }

        return $this->app->environment('production')
            ? new UnconfiguredPaymentGateway
            : new SimulatedPaymentGateway($this->app->make(Cache::class));
    }
}

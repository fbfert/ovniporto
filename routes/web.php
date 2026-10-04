<?php

use App\Domain\Content\Sharing\OgKind;
use App\Http\Controllers\Api\SightingsApiController;
use App\Http\Controllers\Content\CommunityController;
use App\Http\Controllers\Content\CrawlerController;
use App\Http\Controllers\Content\FaqController;
use App\Http\Controllers\Content\LegalPageController;
use App\Http\Controllers\Content\LegendController;
use App\Http\Controllers\Content\OgImageController;
use App\Http\Controllers\Content\PostcardController;
use App\Http\Controllers\Dev\SignInAsController;
use App\Http\Controllers\Dev\StyleguideController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Members\AccountController;
use App\Http\Controllers\Members\GoogleAuthController;
use App\Http\Controllers\Members\WelcomeController;
use App\Http\Controllers\Panel\AuditController;
use App\Http\Controllers\Panel\CampaignAdminController;
use App\Http\Controllers\Panel\ContentHubController;
use App\Http\Controllers\Panel\MemberAdminController;
use App\Http\Controllers\Panel\OrderAdminController;
use App\Http\Controllers\Panel\PanelHomeController;
use App\Http\Controllers\Panel\PlaceAdminController;
use App\Http\Controllers\Panel\ProductAdminController;
use App\Http\Controllers\Panel\RegionAdminController;
use App\Http\Controllers\Panel\SettingsController;
use App\Http\Controllers\Panel\SightingModerationController;
use App\Http\Controllers\Place\ConstructionDiaryController;
use App\Http\Controllers\Place\PlaceController;
use App\Http\Controllers\Place\SupportController;
use App\Http\Controllers\Region\RegionController;
use App\Http\Controllers\Sightings\LogbookController;
use App\Http\Controllers\Sightings\ReportController;
use App\Http\Controllers\Sightings\SightingPhotoController;
use App\Http\Controllers\Store\CartController;
use App\Http\Controllers\Store\CheckoutController;
use App\Http\Controllers\Store\PaymentController;
use App\Http\Controllers\Store\StoreController;
use App\Http\Controllers\WaitlistController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('/lenda', LegendController::class)->name('legend');
Route::get('/faq', FaqController::class)->name('faq');
Route::get('/comunidade', CommunityController::class)->name('community');
Route::get('/postal', PostcardController::class)->name('postcard');
Route::get('/privacidade', LegalPageController::class)->defaults('kind', 'privacy')->name('privacy');
Route::get('/termos', LegalPageController::class)->defaults('kind', 'terms')->name('terms');

Route::get('/o-lugar', PlaceController::class)->name('place');
Route::get('/apoie', SupportController::class)->name('support');
Route::get('/regiao', [RegionController::class, 'index'])->name('region');
Route::get('/regiao/{slug}', [RegionController::class, 'show'])->where('slug', '[a-z0-9-]+')->name('region.partner');
Route::get('/obra', [ConstructionDiaryController::class, 'index'])->name('diary');
Route::get('/obra.rss', [ConstructionDiaryController::class, 'feed'])->name('diary.feed');
Route::get('/obra/{slug}', [ConstructionDiaryController::class, 'show'])
    ->where('slug', '[a-z0-9-]+')
    ->name('diary.post');

// Members: Google is the only way in.
Route::middleware('guest')->group(function () {
    Route::get('/entrar', [GoogleAuthController::class, 'show'])->name('login');
    Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('auth.google');
    Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');
});
Route::middleware('auth')->group(function () {
    Route::post('/sair', [GoogleAuthController::class, 'logout'])->name('logout');
    Route::get('/boas-vindas', [WelcomeController::class, 'show'])->name('welcome');
    Route::post('/boas-vindas', [WelcomeController::class, 'store'])->name('welcome.store');
    Route::get('/apelido-disponivel', [WelcomeController::class, 'nickname'])
        ->middleware('throttle:60,1')
        ->name('nickname.check');

    Route::middleware('profile.complete')->group(function () {
        Route::get('/conta', [AccountController::class, 'show'])->name('account');
        Route::put('/conta/dados', [AccountController::class, 'update'])->name('account.update');
        Route::delete('/conta/relatos/{sighting}', [AccountController::class, 'destroySighting'])
            ->whereNumber('sighting')
            ->name('account.sightings.destroy');
        Route::post('/conta/exportar', [AccountController::class, 'export'])
            ->middleware('throttle:3,60')
            ->name('account.export');
        Route::delete('/conta', [AccountController::class, 'destroy'])->name('account.destroy');

        Route::middleware('not.blocked')->group(function () {
            Route::get('/relatar', [ReportController::class, 'create'])->name('report');
            Route::post('/relatar', [ReportController::class, 'store'])->middleware('throttle:5,1')->name('report.store');
            Route::get('/relatar/enviado', [ReportController::class, 'sent'])->name('report.sent');
            Route::get('/relatar/{sighting}/editar', [ReportController::class, 'edit'])->whereNumber('sighting')->name('report.edit');
            Route::put('/relatar/{sighting}', [ReportController::class, 'update'])
                ->whereNumber('sighting')
                ->middleware('throttle:5,1')
                ->name('report.update');
            Route::post('/relatar/fotos', [ReportController::class, 'upload'])->middleware('throttle:20,1')->name('report.photos.store');
            Route::delete('/relatar/fotos/{upload}', [ReportController::class, 'discard'])->whereUuid('upload')->name('report.photos.destroy');
        });
    });
});

// Operations panel: every area is checked on the server by role (App\Domain\Panel\PanelArea).
Route::middleware(['auth', 'profile.complete', 'panel:inicio'])->prefix('painel')->name('panel')->group(function () {
    Route::get('/', PanelHomeController::class);

    Route::middleware('panel:relatos')->prefix('relatos')->name('.sightings')->group(function () {
        Route::get('/', [SightingModerationController::class, 'index']);
        Route::get('/{sighting}', [SightingModerationController::class, 'show'])->whereNumber('sighting')->name('.show');
        Route::post('/{sighting}/aprovar', [SightingModerationController::class, 'approve'])->whereNumber('sighting')->name('.approve');
        Route::post('/{sighting}/ajuste', [SightingModerationController::class, 'requestChanges'])->whereNumber('sighting')->name('.changes');
        Route::post('/{sighting}/rejeitar', [SightingModerationController::class, 'reject'])->whereNumber('sighting')->name('.reject');
        Route::post('/{sighting}/despublicar', [SightingModerationController::class, 'unpublish'])->whereNumber('sighting')->name('.unpublish');
    });

    Route::get('/auditoria', AuditController::class)->middleware('panel:auditoria')->name('.audit');

    // Areas whose tools arrive with later changes; the role check already applies.
    Route::middleware('panel:membros')->prefix('membros')->name('.members')->group(function () {
        Route::get('/', [MemberAdminController::class, 'index']);
        Route::get('/{member}', [MemberAdminController::class, 'show'])->whereNumber('member')->name('.show');
        Route::put('/{member}/papel', [MemberAdminController::class, 'role'])->whereNumber('member')->name('.role');
        Route::post('/{member}/bloqueio', [MemberAdminController::class, 'block'])->whereNumber('member')->name('.block');
        Route::delete('/{member}/bloqueio', [MemberAdminController::class, 'unblock'])->whereNumber('member')->name('.unblock');
        Route::delete('/{member}', [MemberAdminController::class, 'destroy'])->whereNumber('member')->name('.destroy');
    });

    // Content of the site and of the future place: admin only (PanelArea::Content).
    Route::middleware('panel:conteudo')->group(function () {
        Route::get('/conteudo', ContentHubController::class)->name('.content');
        Route::post('/previa', [SettingsController::class, 'preview'])->name('.preview');

        Route::prefix('configuracoes')->name('.settings')->group(function () {
            Route::get('/', [SettingsController::class, 'show']);
            Route::put('/links', [SettingsController::class, 'links'])->name('.links');
            Route::put('/metas', [SettingsController::class, 'goals'])->name('.goals');
            Route::put('/textos/{key}', [SettingsController::class, 'block'])->where('key', '[a-z_]+')->name('.block');
            Route::post('/{list}', [SettingsController::class, 'saveItem'])->whereIn('list', ['faq', 'regras'])->name('.items.store');
            Route::put('/{list}/{item}', [SettingsController::class, 'saveItem'])->whereIn('list', ['faq', 'regras'])->whereNumber('item')->name('.items.update');
            Route::delete('/{list}/{item}', [SettingsController::class, 'deleteItem'])->whereIn('list', ['faq', 'regras'])->whereNumber('item')->name('.items.destroy');
            Route::post('/{list}/{item}/mover', [SettingsController::class, 'moveItem'])->whereIn('list', ['faq', 'regras'])->whereNumber('item')->name('.items.move');
        });

        Route::prefix('lugar')->name('.place')->group(function () {
            Route::get('/', [PlaceAdminController::class, 'show']);
            Route::put('/espacos/{space}', [PlaceAdminController::class, 'updateSpace'])->whereNumber('space')->name('.spaces.update');
            Route::post('/espacos/{space}/mover', [PlaceAdminController::class, 'moveSpace'])->whereNumber('space')->name('.spaces.move');
            Route::post('/espacos/{space}/conceito', [PlaceAdminController::class, 'concept'])->whereNumber('space')->name('.spaces.concept');
            Route::post('/fotos', [PlaceAdminController::class, 'addPhoto'])->name('.photos.store');
            Route::put('/fotos/{photo}', [PlaceAdminController::class, 'updatePhoto'])->whereNumber('photo')->name('.photos.update');
            Route::delete('/fotos/{photo}', [PlaceAdminController::class, 'deletePhoto'])->whereNumber('photo')->name('.photos.destroy');
            Route::post('/fotos/{photo}/mover', [PlaceAdminController::class, 'movePhoto'])->whereNumber('photo')->name('.photos.move');
            Route::put('/mapa-3d', [PlaceAdminController::class, 'map3d'])->name('.map3d');
        });

        Route::prefix('obra')->name('.diary')->group(function () {
            Route::get('/', [PlaceAdminController::class, 'diary']);
            Route::get('/novo', [PlaceAdminController::class, 'createPost'])->name('.create');
            Route::post('/', [PlaceAdminController::class, 'storePost'])->name('.store');
            Route::get('/{post}', [PlaceAdminController::class, 'editPost'])->whereNumber('post')->name('.edit');
            Route::post('/{post}', [PlaceAdminController::class, 'updatePost'])->whereNumber('post')->name('.update');
            Route::delete('/{post}', [PlaceAdminController::class, 'deletePost'])->whereNumber('post')->name('.destroy');
        });

        Route::prefix('regiao')->name('.region')->group(function () {
            Route::get('/', [RegionAdminController::class, 'index']);
            Route::get('/novo', [RegionAdminController::class, 'create'])->name('.create');
            Route::post('/', [RegionAdminController::class, 'store'])->name('.store');
            Route::post('/localizar', [RegionAdminController::class, 'locate'])->middleware('throttle:30,1')->name('.locate');
            Route::get('/{partner}', [RegionAdminController::class, 'edit'])->whereNumber('partner')->name('.edit');
            Route::post('/{partner}', [RegionAdminController::class, 'update'])->whereNumber('partner')->name('.update');
            Route::post('/{partner}/publicar', [RegionAdminController::class, 'publish'])->whereNumber('partner')->name('.publish');
            Route::post('/{partner}/despublicar', [RegionAdminController::class, 'unpublish'])->whereNumber('partner')->name('.unpublish');
            Route::get('/{partner}/consentimento', [RegionAdminController::class, 'proof'])->whereNumber('partner')->name('.proof');
            Route::delete('/{partner}', [RegionAdminController::class, 'destroy'])->whereNumber('partner')->name('.destroy');
        });

        Route::prefix('campanha')->name('.campaign')->group(function () {
            Route::get('/', [CampaignAdminController::class, 'show']);
            Route::put('/', [CampaignAdminController::class, 'update'])->name('.update');
            Route::post('/apoiadores', [CampaignAdminController::class, 'addSupporter'])->name('.supporters.store');
            Route::post('/apoiadores/importar', [CampaignAdminController::class, 'importSupporters'])->name('.supporters.import');
            Route::delete('/apoiadores/{supporter}', [CampaignAdminController::class, 'deleteSupporter'])->whereNumber('supporter')->name('.supporters.destroy');
            Route::post('/patrocinadores', [CampaignAdminController::class, 'addSponsor'])->name('.sponsors.store');
            Route::delete('/patrocinadores/{sponsor}', [CampaignAdminController::class, 'deleteSponsor'])->whereNumber('sponsor')->name('.sponsors.destroy');
        });

        Route::prefix('avise-me')->name('.waitlist')->group(function () {
            Route::get('/', [CampaignAdminController::class, 'waitlist']);
            Route::get('/exportar', [CampaignAdminController::class, 'exportWaitlist'])->name('.export');
            Route::delete('/{subscriber}', [CampaignAdminController::class, 'removeSubscriber'])->whereNumber('subscriber')->name('.destroy');
        });
    });

    Route::middleware('panel:pedidos')->prefix('pedidos')->name('.orders')->group(function () {
        Route::get('/', [OrderAdminController::class, 'index']);
        Route::get('/exportar', [OrderAdminController::class, 'export'])->name('.export');
        Route::get('/{number}', [OrderAdminController::class, 'show'])->where('number', 'OVP-\d{4}-\d{6}')->name('.show');
        Route::get('/{number}/ordem-de-producao.pdf', [OrderAdminController::class, 'productionPdf'])->where('number', 'OVP-\d{4}-\d{6}')->name('.pdf');
        Route::post('/{number}/cpf', [OrderAdminController::class, 'cpf'])->where('number', 'OVP-\d{4}-\d{6}')->middleware('throttle:30,1')->name('.cpf');
        Route::post('/{number}/producao', [OrderAdminController::class, 'production'])->where('number', 'OVP-\d{4}-\d{6}')->name('.production');
        Route::post('/{number}/etiqueta', [OrderAdminController::class, 'label'])->where('number', 'OVP-\d{4}-\d{6}')->name('.label');
        Route::post('/{number}/enviado', [OrderAdminController::class, 'ship'])->where('number', 'OVP-\d{4}-\d{6}')->name('.ship');
        Route::post('/{number}/entregue', [OrderAdminController::class, 'deliver'])->where('number', 'OVP-\d{4}-\d{6}')->name('.deliver');
        Route::post('/{number}/cancelar', [OrderAdminController::class, 'cancel'])->where('number', 'OVP-\d{4}-\d{6}')->name('.cancel');
        Route::post('/{number}/reembolsar', [OrderAdminController::class, 'refund'])->where('number', 'OVP-\d{4}-\d{6}')->name('.refund');
    });

    Route::middleware('panel:produtos')->prefix('produtos')->name('.products')->group(function () {
        Route::get('/', [ProductAdminController::class, 'index']);
        Route::get('/novo', [ProductAdminController::class, 'create'])->name('.create');
        Route::post('/', [ProductAdminController::class, 'store'])->name('.store');
        Route::get('/{product}', [ProductAdminController::class, 'edit'])->whereNumber('product')->name('.edit');
        Route::put('/{product}', [ProductAdminController::class, 'update'])->whereNumber('product')->name('.update');
        Route::post('/{product}/variantes', [ProductAdminController::class, 'addVariant'])->whereNumber('product')->name('.variants.store');
        Route::put('/{product}/variantes/{variant}', [ProductAdminController::class, 'updateVariant'])->whereNumber(['product', 'variant'])->name('.variants.update');
        Route::post('/{product}/variantes/{variant}/estoque', [ProductAdminController::class, 'adjustStock'])->whereNumber(['product', 'variant'])->name('.variants.stock');
        Route::post('/{product}/imagens', [ProductAdminController::class, 'addImage'])->whereNumber('product')->name('.images.store');
        Route::put('/{product}/imagens/{image}', [ProductAdminController::class, 'updateImage'])->whereNumber(['product', 'image'])->name('.images.update');
        Route::delete('/{product}/imagens/{image}', [ProductAdminController::class, 'deleteImage'])->whereNumber(['product', 'image'])->name('.images.destroy');
        Route::post('/{product}/imagens/{image}/mover', [ProductAdminController::class, 'moveImage'])->whereNumber(['product', 'image'])->name('.images.move');
    });
});

// Store: only active products are public; prices always come from the server.
Route::get('/loja', [StoreController::class, 'index'])->name('store');
Route::get('/loja/{slug}', [StoreController::class, 'show'])->where('slug', '[a-z0-9-]+')->name('store.product');
Route::post('/loja/frete', [StoreController::class, 'shipping'])->middleware('throttle:20,1')->name('store.shipping');
Route::post('/carrinho/itens', [CartController::class, 'add'])->middleware('throttle:60,1')->name('cart.add');
Route::patch('/carrinho/itens/{variant}', [CartController::class, 'update'])->whereNumber('variant')->middleware('throttle:60,1')->name('cart.update');
Route::delete('/carrinho/itens/{variant}', [CartController::class, 'remove'])->whereNumber('variant')->name('cart.remove');

// Checkout: visitors and members; card data never reaches these routes (the provider's components take it).
Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout');
Route::post('/checkout/identificacao', [CheckoutController::class, 'identify'])->middleware('throttle:30,1')->name('checkout.identify');
Route::post('/checkout/entrega', [CheckoutController::class, 'delivery'])->middleware('throttle:30,1')->name('checkout.delivery');
Route::post('/checkout', [CheckoutController::class, 'place'])->middleware('throttle:10,1')->name('checkout.place');
Route::get('/pedido/{number}/pagar', [CheckoutController::class, 'pay'])->where('number', 'OVP-\d{4}-\d{6}')->name('order.pay');
Route::post('/pedido/{number}/pagamento', [PaymentController::class, 'start'])->where('number', 'OVP-\d{4}-\d{6}')->middleware('throttle:20,1')->name('order.payment.start');
Route::post('/pedido/{number}/aprovar', [PaymentController::class, 'approve'])->where('number', 'OVP-\d{4}-\d{6}')->middleware('throttle:20,1')->name('order.payment.approve');
Route::get('/pedido/{number}', [PaymentController::class, 'show'])->where('number', 'OVP-\d{4}-\d{6}')->name('order.show');
Route::post('/webhooks/paypal', [PaymentController::class, 'webhook'])->middleware('throttle:120,1')->name('webhooks.paypal');

// Livro de avistamentos: only approved reports are public.
Route::get('/mapa', [LogbookController::class, 'index'])->name('logbook');
Route::get('/relatos/{sighting}', [LogbookController::class, 'show'])->whereNumber('sighting')->name('sightings.show');
Route::get('/api/sightings', SightingsApiController::class)->middleware('throttle:60,1')->name('api.sightings');

// Report photos: approved ones are public; pending ones need a signed URL and the author or a moderator.
Route::get('/fotos/relatos/{photo}/{width}', SightingPhotoController::class)
    ->whereNumber(['photo', 'width'])
    ->name('sighting.photo');

Route::post('/avise-me', [WaitlistController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('waitlist.store');
Route::get('/avise-me/confirmar/{subscriber}', [WaitlistController::class, 'confirm'])
    ->whereNumber('subscriber')
    ->middleware('signed')
    ->name('waitlist.confirm');

Route::get('/sitemap.xml', [CrawlerController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [CrawlerController::class, 'robots'])->name('robots');

// Link previews of public content (public/og/default.jpg is a static file and never reaches here).
Route::get('/og/{kind}/{key}.{version}.jpg', OgImageController::class)
    ->whereIn('kind', array_column(OgKind::cases(), 'value'))
    ->where(['key' => '[a-z0-9-]+', 'version' => '[a-f0-9]{10}'])
    ->middleware('throttle:120,1')
    ->name('og.image');

Route::get('/dev/styleguide', StyleguideController::class)->name('dev.styleguide');
Route::get('/dev/entrar-como/{member}', SignInAsController::class)->whereNumber('member')->name('dev.sign-in-as');

<?php

use App\Http\Controllers\Api\SightingsApiController;
use App\Http\Controllers\Content\CommunityController;
use App\Http\Controllers\Content\FaqController;
use App\Http\Controllers\Content\LegalPageController;
use App\Http\Controllers\Content\LegendController;
use App\Http\Controllers\Dev\SignInAsController;
use App\Http\Controllers\Dev\StyleguideController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Members\AccountController;
use App\Http\Controllers\Members\GoogleAuthController;
use App\Http\Controllers\Members\WelcomeController;
use App\Http\Controllers\Place\ConstructionDiaryController;
use App\Http\Controllers\Place\PlaceController;
use App\Http\Controllers\Place\SupportController;
use App\Http\Controllers\Region\RegionController;
use App\Http\Controllers\Sightings\LogbookController;
use App\Http\Controllers\Sightings\ReportController;
use App\Http\Controllers\Sightings\SightingPhotoController;
use App\Http\Controllers\UpcomingPageController;
use App\Http\Controllers\WaitlistController;
use App\Support\UpcomingPages;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('/lenda', LegendController::class)->name('legend');
Route::get('/faq', FaqController::class)->name('faq');
Route::get('/comunidade', CommunityController::class)->name('community');
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

        Route::get('/relatar', [ReportController::class, 'create'])->name('report');
        Route::post('/relatar', [ReportController::class, 'store'])->middleware('throttle:5,1')->name('report.store');
        Route::get('/relatar/enviado', [ReportController::class, 'sent'])->name('report.sent');
        Route::post('/relatar/fotos', [ReportController::class, 'upload'])->middleware('throttle:20,1')->name('report.photos.store');
        Route::delete('/relatar/fotos/{upload}', [ReportController::class, 'discard'])->whereUuid('upload')->name('report.photos.destroy');
    });
});

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

// Menu destinations built by later OpenSpec changes (openspec/changes/add-*).
foreach (array_keys(UpcomingPages::PAGES) as $slug) {
    Route::get($slug, UpcomingPageController::class)->name("upcoming.{$slug}");
}

Route::get('/dev/styleguide', StyleguideController::class)->name('dev.styleguide');
Route::get('/dev/entrar-como/{member}', SignInAsController::class)->whereNumber('member')->name('dev.sign-in-as');

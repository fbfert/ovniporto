<?php

use App\Http\Controllers\Content\CommunityController;
use App\Http\Controllers\Content\FaqController;
use App\Http\Controllers\Content\LegalPageController;
use App\Http\Controllers\Content\LegendController;
use App\Http\Controllers\Dev\StyleguideController;
use App\Http\Controllers\HomeController;
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

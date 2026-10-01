<?php

use App\Http\Controllers\Dev\StyleguideController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\UpcomingPageController;
use App\Http\Controllers\WaitlistController;
use App\Support\UpcomingPages;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

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

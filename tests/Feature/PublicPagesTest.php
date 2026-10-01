<?php

use App\Support\UpcomingPages;
use Inertia\Testing\AssertableInertia as Assert;

it('renders an honest "em construção" page for every menu destination', function (string $slug) {
    $this->get("/{$slug}")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('ComingSoon')
            ->where('slug', $slug)
            ->where('title', UpcomingPages::PAGES[$slug]['title'])
        );
})->with(array_keys(UpcomingPages::PAGES));

it('never asks for money on /apoie while the budget is being planned', function () {
    $this->get('/apoie')
        ->assertOk()
        ->assertDontSee('paypal', false)
        ->assertDontSee('Apoiar no', false);
});

it('shows the themed 404 page', function () {
    $this->get('/um-ponto-do-ceu-que-nao-existe')
        ->assertNotFound()
        ->assertInertia(fn (Assert $page) => $page->component('Errors/Error')->where('status', 404));
});

it('keeps the styleguide out of production', function () {
    app()->detectEnvironment(fn () => 'production');

    $this->get('/dev/styleguide')->assertNotFound();
});

it('serves the styleguide locally', function () {
    app()->detectEnvironment(fn () => 'local');

    $this->get('/dev/styleguide')->assertOk();
});

it('emits sharing metadata from the server', function () {
    config(['inertia.ssr.enabled' => false]);

    $this->get('/')->assertSee('<title', false);
});

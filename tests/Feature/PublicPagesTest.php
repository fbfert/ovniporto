<?php

use App\Application\Content\UseCases\GetCommunityLinks;
use App\Models\ContentBlock;
use Inertia\Testing\AssertableInertia as Assert;

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

it('shares the community links with every page, null while not registered', function () {
    ContentBlock::query()->updateOrCreate(['key' => 'link_whatsapp'], ['value' => 'https://chat.whatsapp.com/vigilia']);

    $this->get('/origem')->assertInertia(fn (Assert $page) => $page
        ->where('community.whatsapp', 'https://chat.whatsapp.com/vigilia')
        ->where('community.instagram', null)
        ->where('community.email', GetCommunityLinks::FALLBACK_EMAIL)
    );
});

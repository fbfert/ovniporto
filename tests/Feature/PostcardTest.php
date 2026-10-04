<?php

use Inertia\Testing\AssertableInertia as Assert;

beforeEach(fn () => config(['app.url' => 'https://ovniporto.test', 'inertia.ssr.enabled' => false]));

it('opens the postcard page with the image to download and the link to share', function () {
    $this->get('/postal')->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('Postcard/Show')
        ->where('shareUrl', 'https://ovniporto.test/postal')
        ->where('imageUrl', '/brand/postal.jpg')
        ->where('seo.title', 'Mande um postal')
        ->where('seo.image', 'https://ovniporto.test/brand/postal-og.jpg')
    );
});

it('ships the postcard files the page points to', function () {
    expect(getimagesize(public_path('brand/postal.jpg')))->toMatchArray([0 => 1500, 1 => 1000])
        ->and(getimagesize(public_path('brand/postal-og.jpg')))->toMatchArray([0 => 1200, 1 => 630])
        ->and(public_path('brand/postal-800.webp'))->toBeFile()
        ->and(public_path('brand/postal-1500.webp'))->toBeFile();
});

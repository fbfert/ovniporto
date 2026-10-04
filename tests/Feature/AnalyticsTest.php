<?php

beforeEach(fn () => config([
    'inertia.ssr.enabled' => false,
    'services.umami.script_url' => 'https://metrica.ovniporto.test/script.js',
    'services.umami.website_id' => '6f1c0d1e-0000-4000-8000-000000000000',
]));

it('leaves the metric script out outside production', function () {
    app()->detectEnvironment(fn () => 'local');

    $this->get('/')->assertOk()->assertDontSee('metrica.ovniporto.test', false);
});

it('loads the cookieless metric in production, respecting Do Not Track', function () {
    app()->detectEnvironment(fn () => 'production');

    $this->get('/')->assertOk()->assertSee(
        '<script defer src="https://metrica.ovniporto.test/script.js" data-website-id="6f1c0d1e-0000-4000-8000-000000000000" data-do-not-track="true"></script>',
        false,
    );
});

it('loads nothing in production while Umami is not configured', function () {
    app()->detectEnvironment(fn () => 'production');
    config(['services.umami.website_id' => null]);

    $this->get('/')->assertOk()->assertDontSee('metrica.ovniporto.test', false);
});

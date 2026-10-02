<?php

use App\Models\RegionPartner;
use Inertia\Testing\AssertableInertia as Assert;

function partner(array $attributes = []): RegionPartner
{
    static $n = 0;
    $n++;

    return RegionPartner::query()->create([
        'name' => "Parceiro {$n}",
        'slug' => "parceiro-{$n}",
        'type' => 'inn',
        'city' => 'Lages',
        'lat' => -27.74345,
        'lng' => -50.21841,
        'consent_given_at' => now()->subMonth(),
        'published_at' => now()->subDay(),
        ...$attributes,
    ]);
}

it('shows the empty state while no partner is published', function () {
    partner(['consent_given_at' => null]);

    $this->get('/regiao')->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('Region/Index')
        ->has('partners', 0)
        ->where('total', 0)
        ->where('community.email', 'contato@ovniporto.tars.art.br')
    );
});

it('lists published partners with their distance to the OVNIPORTO', function () {
    partner(['name' => 'Pousada da Neblina']);

    $this->get('/regiao')->assertInertia(fn (Assert $page) => $page
        ->has('partners', 1)
        ->where('partners.0.name', 'Pousada da Neblina')
        ->where('partners.0.distanceKm', 12)
        ->where('origin', ['lat' => -27.85495, 'lng' => -50.21841])
    );
});

it('filters by type and keeps the filter in the URL props', function () {
    partner(['name' => 'Pousada', 'type' => 'inn']);
    partner(['name' => 'Vinícola', 'type' => 'producer']);

    $this->get('/regiao?tipo=producer')->assertInertia(fn (Assert $page) => $page
        ->has('partners', 1)
        ->where('partners.0.name', 'Vinícola')
        ->where('filters.tipo', 'producer')
        ->where('total', 2)
    );
});

it('searches name, city and description', function () {
    partner(['name' => 'Pousada', 'city' => 'Urupema']);
    partner(['name' => 'Trilha', 'city' => 'Lages', 'short_description' => 'Cânion e cachoeira']);

    $this->get('/regiao?q=cachoeira')->assertInertia(fn (Assert $page) => $page->has('partners', 1)->where('partners.0.name', 'Trilha'));
    $this->get('/regiao?q=urupema')->assertInertia(fn (Assert $page) => $page->has('partners', 1)->where('partners.0.name', 'Pousada'));
});

it('opens a published partner and hides one without consent', function () {
    $published = partner(['instagram' => null, 'whatsapp' => '49999990000']);
    $hidden = partner(['consent_given_at' => null]);

    $this->get("/regiao/{$published->slug}")->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('Region/Show')
        ->where('partner.slug', $published->slug)
        ->where('partner.instagram', null)
        ->where('partner.whatsapp', '49999990000')
    );
    $this->get("/regiao/{$hidden->slug}")->assertNotFound();
});

it('keeps scheduled partners off the list and the page', function () {
    $scheduled = partner(['published_at' => now()->addWeek()]);

    $this->get('/regiao')->assertInertia(fn (Assert $page) => $page->has('partners', 0));
    $this->get("/regiao/{$scheduled->slug}")->assertNotFound();
});

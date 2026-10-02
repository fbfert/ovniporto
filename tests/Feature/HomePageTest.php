<?php

use App\Models\RegionPartner;
use App\Models\Sighting;
use Database\Seeders\DatabaseSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(fn () => $this->seed(DatabaseSeeder::class));

it('renders the home with the brand name and shared metadata', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('OVNIPORTO')
        ->assertInertia(fn (Assert $page) => $page
            ->component('Home')
            ->has('content.home_intro')
            ->where('content.legend_body', null)
            ->has('spaces', 9)
            ->where('spaces.0.status', 'planning')
        );
});

it('gives each space its concept illustration, and only ones that exist', function () {
    $manifest = json_decode((string) file_get_contents(resource_path('js/data/concept.json')), true);

    $this->get('/')->assertInertia(fn (Assert $page) => $page
        ->where('spaces.1.concept', 'vigil')
        ->where('spaces', fn ($spaces) => collect($spaces)
            ->pluck('concept')
            ->filter()
            ->every(fn (string $slug) => isset($manifest[$slug]) && is_file(public_path("concept/{$slug}.jpg"))))
    );
});

it('counts and lists only approved sightings', function () {
    Sighting::factory()->count(2)->approved()->create();
    Sighting::factory()->count(3)->create();

    $this->get('/')->assertInertia(fn (Assert $page) => $page
        ->where('counters.sightings', 2)
        ->has('sightings', 2)
    );
});

it('shows at most four sightings, newest first', function () {
    Sighting::factory()->count(6)->approved()->create();
    $newest = Sighting::factory()->approved()->create(['published_at' => now()]);

    $this->get('/')->assertInertia(fn (Assert $page) => $page
        ->has('sightings', 4)
        ->where('sightings.0.id', $newest->id)
    );
});

it('lists only the products that exist: the sticker', function () {
    $this->get('/')->assertInertia(fn (Assert $page) => $page
        ->has('products', 1)
        ->where('products.0.slug', 'adesivo-ovniporto')
        ->where('products.0.priceCents', 800)
    );
});

it('hides partners without consent', function () {
    RegionPartner::query()->create([
        'name' => 'Pousada sem consentimento', 'slug' => 'sem', 'type' => 'inn', 'city' => 'Lages',
        'is_featured' => true, 'published_at' => now()->subDay(),
    ]);
    RegionPartner::query()->create([
        'name' => 'Pousada com consentimento', 'slug' => 'com', 'type' => 'inn', 'city' => 'Lages',
        'is_featured' => true, 'published_at' => now()->subDay(), 'consent_given_at' => now()->subWeek(),
    ]);

    $this->get('/')->assertInertia(fn (Assert $page) => $page
        ->has('partners', 1)
        ->where('partners.0.slug', 'com')
    );
});

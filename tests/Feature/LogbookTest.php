<?php

use App\Models\Member;
use App\Models\Sighting;
use Inertia\Testing\AssertableInertia as Assert;

it('keeps pending, rejected and changes-requested reports out of the API', function () {
    $public = Sighting::factory()->approved()->create();
    Sighting::factory()->create(); // pending
    Sighting::factory()->create(['status' => 'rejected']);
    Sighting::factory()->create(['status' => 'changes_requested']);

    $this->getJson('/api/sightings')
        ->assertOk()
        ->assertHeader('Cache-Control', 'max-age=60, public')
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $public->id);
});

it('answers the API with exactly the seven public fields', function () {
    Sighting::factory()->approved()->create(['description' => 'Texto que não sai pela API']);

    $item = $this->getJson('/api/sightings')->json('data.0');

    expect(array_keys($item))->toEqualCanonicalizing(['id', 'type', 'lat', 'lng', 'date', 'nickname', 'thumb'])
        ->and(json_encode($item))->not->toContain('Texto que não sai');
});

it('filters the API by period and type', function () {
    Sighting::factory()->approved()->create(['type' => 'light', 'observed_date' => now()->subDays(5)]);
    Sighting::factory()->approved()->create(['type' => 'object', 'observed_date' => now()->subDays(5)]);
    Sighting::factory()->approved()->create(['type' => 'light', 'observed_date' => now()->subMonths(4)]);

    $this->getJson('/api/sightings?periodo=30d&tipo=light')->assertJsonCount(1, 'data');
    $this->getJson('/api/sightings?periodo=6m&tipo=light')->assertJsonCount(2, 'data');
    $this->getJson('/api/sightings?periodo=all')->assertJsonCount(3, 'data');
});

it('shows a freshly approved report despite the cache', function () {
    $pending = Sighting::factory()->create();
    $this->getJson('/api/sightings')->assertJsonCount(0, 'data');

    $pending->update(['status' => 'approved', 'published_at' => now()]);

    $this->getJson('/api/sightings')->assertJsonCount(1, 'data');
});

it('opens the Livro with the approved count and the filters from the URL', function () {
    Sighting::factory()->count(3)->approved()->create(['type' => 'trail']);
    Sighting::factory()->approved()->create(['type' => 'light']);
    Sighting::factory()->create(['type' => 'trail']);

    $this->get('/mapa?tipo=trail&periodo=all')->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('Sightings/Logbook')
        ->where('total', 3)
        ->where('filters', ['periodo' => 'all', 'tipo' => 'trail'])
        ->has('cards', 3)
        ->has('pins', 3)
        ->where('hasMore', false)
    );
});

it('pages the polaroids twelve at a time', function () {
    Sighting::factory()->count(14)->approved()->create();

    $this->get('/mapa')->assertInertia(fn (Assert $page) => $page->has('cards', 12)->where('hasMore', true));
    $this->get('/mapa?pagina=2')->assertInertia(fn (Assert $page) => $page->has('cards', 2)->where('hasMore', false));
});

it('shows an approved report to anyone and hides the others', function () {
    $approved = Sighting::factory()->approved()->create(['public_nickname' => 'coruja']);
    $pending = Sighting::factory()->create();

    $this->get("/relatos/{$approved->id}")->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('Sightings/Show')
        ->where('sighting.nickname', 'coruja')
        ->where('ownPending', false)
    );
    $this->get("/relatos/{$pending->id}")->assertNotFound();
    $this->actingAs(Member::factory()->create())->get("/relatos/{$pending->id}")->assertNotFound();
});

it('lets the author see their own report while it is in analysis', function () {
    $author = Member::factory()->create();
    $pending = Sighting::factory()->create(['member_id' => $author->id]);

    $this->actingAs($author)->get("/relatos/{$pending->id}")->assertOk()->assertInertia(fn (Assert $page) => $page
        ->where('ownPending', true)
        ->where('sighting.status', 'pending')
    );
});

it('lists up to three approved reports within 20 km, nearest first', function () {
    $here = Sighting::factory()->approved()->create(['lat' => -27.8500, 'lng' => -50.2100]);
    $near = Sighting::factory()->approved()->create(['lat' => -27.8600, 'lng' => -50.2100]);   // ~1 km
    $mid = Sighting::factory()->approved()->create(['lat' => -27.9500, 'lng' => -50.2100]);    // ~11 km
    Sighting::factory()->approved()->create(['lat' => -28.1500, 'lng' => -50.2100]);           // ~33 km
    Sighting::factory()->create(['lat' => -27.8510, 'lng' => -50.2100]);                       // pending

    $this->get("/relatos/{$here->id}")->assertInertia(fn (Assert $page) => $page
        ->has('nearby', 2)
        ->where('nearby.0.id', $near->id)
        ->where('nearby.1.id', $mid->id)
    );
});

it('shows an empty "nearby" when the report has no neighbours', function () {
    $alone = Sighting::factory()->approved()->create(['lat' => -27.0, 'lng' => -49.0]);

    $this->get("/relatos/{$alone->id}")->assertInertia(fn (Assert $page) => $page->has('nearby', 0));
});

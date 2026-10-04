<?php

use App\Http\Middleware\HandleInertiaRequests;
use App\Models\ContentBlock;
use App\Models\Faq;
use App\Models\Product;
use Database\Seeders\ProductSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(fn () => config(['inertia.ssr.enabled' => false]));

it('answers 304 when the page did not change, 200 with a new ETag when it did', function () {
    Faq::query()->create(['question' => 'Já existe?', 'answer' => 'Ainda não.', 'sort_order' => 0]);

    $first = $this->get('/faq')->assertOk();
    $etag = $first->headers->get('ETag');
    expect($etag)->not->toBeEmpty()
        ->and($first->headers->get('Cache-Control'))->toContain('no-cache')->toContain('private');

    $this->withHeader('If-None-Match', $etag)->get('/faq')->assertStatus(304)->assertContent('');

    Faq::query()->create(['question' => 'É de graça?', 'answer' => 'Sim.', 'sort_order' => 1]);
    $changed = $this->withHeader('If-None-Match', $etag)->get('/faq')->assertOk();
    expect($changed->headers->get('ETag'))->not->toBe($etag);
});

it('revalidates Inertia visits too', function () {
    $headers = ['X-Inertia' => 'true', 'X-Inertia-Version' => (string) app(HandleInertiaRequests::class)->version(request())];
    $etag = $this->withHeaders($headers)->get('/lenda')->assertOk()->headers->get('ETag');

    $this->withHeaders([...$headers, 'If-None-Match' => $etag])->get('/lenda')->assertStatus(304);
});

it('keeps the caching chosen by public resources', function () {
    expect($this->get('/api/sightings')->headers->get('Cache-Control'))->toContain('public')
        ->and($this->get('/robots.txt')->headers->has('ETag'))->toBeTrue();
});

it('serves the home from its fragment cache and refreshes it when a product changes', function () {
    $this->seed(ProductSeeder::class);
    Product::query()->update(['is_featured' => true]);
    $this->get('/')->assertInertia(fn (Assert $page) => $page->where('products.0.name', 'Adesivo OVNIPORTO'));

    Product::query()->where('slug', 'adesivo-ovniporto')->firstOrFail()->update(['name' => 'Adesivo Lages']);

    $this->get('/')->assertInertia(fn (Assert $page) => $page->where('products.0.name', 'Adesivo Lages'));
});

it('refreshes the home when the panel edits its texts', function () {
    $this->get('/')->assertOk();
    ContentBlock::query()->updateOrCreate(['key' => 'home_intro'], ['value' => 'Texto novo da home']);

    $this->get('/')->assertInertia(fn (Assert $page) => $page->where('content.home_intro', 'Texto novo da home'));
});

it('serves the cached home from the database store, as in production', function () {
    config(['cache.default' => 'database']);
    $this->seed(ProductSeeder::class);

    $this->get('/')->assertOk();
    expect(DB::table('cache')->where('key', 'like', '%fragment:home:%:data')->exists())->toBeTrue();

    $this->get('/')->assertOk()->assertInertia(fn (Assert $page) => $page->has('products')->has('counters'));
});

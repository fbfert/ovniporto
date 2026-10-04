<?php

use App\Application\Content\UseCases\BuildSitemap;
use App\Infrastructure\Content\SitemapFile;
use App\Models\ConstructionPost;
use App\Models\Product;
use App\Models\RegionPartner;
use App\Models\Sighting;
use Database\Seeders\ProductSeeder;

beforeEach(function () {
    config(['app.url' => 'https://ovniporto.test']);
    $this->sitemapPath = sys_get_temp_dir().'/ovniporto-sitemap-'.uniqid().'.xml';
    $this->app->bind(SitemapFile::class, fn ($app) => new SitemapFile($app->make(BuildSitemap::class), $this->sitemapPath));
});

afterEach(fn () => @unlink($this->sitemapPath));

/** @return list<string> */
function sitemapUrls(): array
{
    $xml = simplexml_load_string(test()->get('/sitemap.xml')->assertOk()->assertHeader('Content-Type', 'application/xml; charset=UTF-8')->streamedContent());

    return array_map(fn ($url) => (string) $url->loc, iterator_to_array($xml->url, false));
}

it('lists the public pages and every published content, and nothing private', function () {
    $this->seed(ProductSeeder::class);
    Product::query()->where('slug', 'caneca-ovniporto')->update(['is_active' => false]);
    $approved = Sighting::factory()->approved()->create();
    $pending = Sighting::factory()->create();
    RegionPartner::query()->create(['name' => 'Pousada', 'slug' => 'pousada', 'type' => 'inn', 'city' => 'Lages', 'consent_given_at' => now(), 'published_at' => now()->subDay()]);
    RegionPartner::query()->create(['name' => 'Sem aceite', 'slug' => 'sem-aceite', 'type' => 'inn', 'city' => 'Lages', 'published_at' => now()->subDay()]);
    ConstructionPost::query()->create(['title' => 'Pedra', 'slug' => 'pedra', 'body' => 'x', 'phase' => 1, 'published_at' => now()->subDay()]);
    ConstructionPost::query()->create(['title' => 'Rascunho', 'slug' => 'rascunho', 'body' => 'x', 'phase' => 1]);

    $urls = sitemapUrls();

    expect($urls)->toContain('https://ovniporto.test', 'https://ovniporto.test/loja', 'https://ovniporto.test/postal')
        ->toContain("https://ovniporto.test/relatos/{$approved->id}", 'https://ovniporto.test/loja/adesivo-ovniporto')
        ->toContain('https://ovniporto.test/regiao/pousada', 'https://ovniporto.test/obra/pedra')
        ->not->toContain("https://ovniporto.test/relatos/{$pending->id}")
        ->not->toContain('https://ovniporto.test/loja/caneca-ovniporto')
        ->not->toContain('https://ovniporto.test/regiao/sem-aceite')
        ->not->toContain('https://ovniporto.test/obra/rascunho');
    foreach ($urls as $url) {
        expect($url)->not->toContain('/painel')->not->toContain('/conta');
    }
});

it('is served from the file the scheduler writes', function () {
    $this->artisan('sitemap:generate')->assertSuccessful();
    ConstructionPost::query()->create(['title' => 'Nova', 'slug' => 'nova', 'body' => 'x', 'phase' => 1, 'published_at' => now()->subMinute()]);

    expect(sitemapUrls())->not->toContain('https://ovniporto.test/obra/nova');

    $this->artisan('sitemap:generate')->assertSuccessful();
    expect(sitemapUrls())->toContain('https://ovniporto.test/obra/nova');
});

it('keeps the panel and the account out of search engines in production', function () {
    app()->detectEnvironment(fn () => 'production');

    $this->get('/robots.txt')->assertOk()
        ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
        ->assertSee("Disallow: /painel\n", false)
        ->assertSee("Disallow: /conta\n", false)
        ->assertDontSee("Disallow: /\n", false)
        ->assertSee('Sitemap: https://ovniporto.test/sitemap.xml', false);
});

it('keeps the whole staging site out of search engines', function () {
    app()->detectEnvironment(fn () => 'staging');

    $this->get('/robots.txt')->assertSee("Disallow: /\n", false);
});

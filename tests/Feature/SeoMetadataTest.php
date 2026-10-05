<?php

use App\Models\ConstructionPost;
use App\Models\Faq;
use App\Models\Member;
use App\Models\RegionPartner;
use App\Models\Sighting;
use Database\Seeders\ProductSeeder;

beforeEach(function () {
    config(['app.url' => 'https://ovniporto.test', 'inertia.ssr.enabled' => false]);
    URL::forceRootUrl('https://ovniporto.test');
});

/** @return array<string, string> meta name/property => content, plus title and canonical */
function previewTags(string $html): array
{
    $dom = new DOMDocument;
    @$dom->loadHTML('<?xml encoding="utf-8"?>'.$html);
    $tags = ['title' => trim((string) $dom->getElementsByTagName('title')->item(0)?->textContent)];
    foreach ($dom->getElementsByTagName('meta') as $meta) {
        $key = $meta->getAttribute('property') ?: $meta->getAttribute('name');
        if ($key !== '') {
            $tags[$key] = $meta->getAttribute('content');
        }
    }
    foreach ($dom->getElementsByTagName('link') as $link) {
        if ($link->getAttribute('rel') === 'canonical') {
            $tags['canonical'] = $link->getAttribute('href');
        }
    }

    return $tags;
}

/** @return list<array<string, mixed>> */
function jsonLd(string $html): array
{
    preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $html, $matches);

    return array_map(fn (string $json) => json_decode($json, true, flags: JSON_THROW_ON_ERROR), $matches[1]);
}

function publicContent(): void
{
    test()->seed(ProductSeeder::class);
    RegionPartner::query()->create([
        'name' => 'Pousada da Coxilha', 'slug' => 'pousada-da-coxilha', 'type' => 'inn', 'city' => 'Lages',
        'short_description' => 'Café colonial e céu escuro a 8 km da pista.',
        'consent_given_at' => now()->subMonth(), 'published_at' => now()->subDay(),
    ]);
    ConstructionPost::query()->create([
        'title' => 'A primeira pedra', 'slug' => 'a-primeira-pedra', 'excerpt' => 'Marcamos o eixo da pista.',
        'body' => 'Texto.', 'phase' => 1, 'published_at' => now()->subDay(),
    ]);
}

it('prints title, unique description, canonical and preview image on every public page, without JavaScript', function () {
    publicContent();
    $sighting = Sighting::factory()->approved()->create();

    $pages = [
        '/' => 'OVNIPORTO Lages · A pista de pouso do planalto',
        '/origem' => 'A origem · OVNIPORTO Lages',
        '/faq' => 'Perguntas frequentes · OVNIPORTO Lages',
        '/comunidade' => 'Comunidade · OVNIPORTO Lages',
        '/privacidade' => 'Privacidade · OVNIPORTO Lages',
        '/termos' => 'Termos de uso · OVNIPORTO Lages',
        '/o-lugar' => 'O lugar · OVNIPORTO Lages',
        '/apoie' => 'Apoie a pista · OVNIPORTO Lages',
        '/regiao' => 'Conheça a região · OVNIPORTO Lages',
        '/regiao/pousada-da-coxilha' => 'Pousada da Coxilha · OVNIPORTO Lages',
        '/obra' => 'Diário da obra · OVNIPORTO Lages',
        '/obra/a-primeira-pedra' => 'A primeira pedra · OVNIPORTO Lages',
        '/mapa' => 'Livro de avistamentos · OVNIPORTO Lages',
        "/relatos/{$sighting->id}" => null,
        '/loja' => 'Loja · OVNIPORTO Lages',
        '/loja/adesivo-ovniporto' => 'Adesivo OVNIPORTO · OVNIPORTO Lages',
    ];

    $descriptions = [];
    foreach ($pages as $path => $title) {
        $tags = previewTags($this->get($path)->assertOk()->getContent());

        if ($title !== null) {
            expect($tags['title'])->toBe($title, $path)->and($tags['og:title'])->toBe($title, $path);
        }
        expect($tags['description'])->not->toBeEmpty()
            ->and($tags['og:description'])->toBe($tags['description'])
            ->and($tags['canonical'])->toBe($path === '/' ? 'https://ovniporto.test' : "https://ovniporto.test{$path}")
            ->and($tags['og:image'])->toStartWith('https://ovniporto.test/')
            ->and($tags['og:image:width'])->toBe('1200')
            ->and($tags['twitter:card'])->toBe('summary_large_image')
            ->and($tags['robots'])->toBe('index, follow');
        $descriptions[$path] = $tags['description'];
    }

    expect(array_unique($descriptions))->toHaveCount(count($descriptions));
});

it('gives content pages their own preview image', function () {
    publicContent();

    $tags = previewTags($this->get('/loja/adesivo-ovniporto')->getContent());

    expect($tags['og:image'])->toMatch('#^https://ovniporto\.test/og/produto/adesivo-ovniporto\.[a-f0-9]{10}\.jpg$#');
});

it('keeps private and transactional pages out of the index', function () {
    $this->get('/entrar')->assertOk()->assertSee('<meta name="robots" content="noindex, nofollow">', false);
    $this->actingAs(Member::factory()->create())->get('/conta')
        ->assertOk()
        ->assertSee('<meta name="robots" content="noindex, nofollow">', false)
        ->assertSee('<title>Minha conta · OVNIPORTO Lages</title>', false);
});

it('titles the author view of a pending report without exposing it to search engines', function () {
    $member = Member::factory()->create();
    $sighting = Sighting::factory()->create(['member_id' => $member->id]);

    $html = $this->actingAs($member)->get("/relatos/{$sighting->id}")->assertOk()->getContent();
    $tags = previewTags($html);

    expect($tags['title'])->toBe('Relato em análise · OVNIPORTO Lages')
        ->and($tags['robots'])->toBe('noindex, nofollow')
        ->and($tags['og:image'])->toBe('https://ovniporto.test/og/default.jpg')
        ->and($tags['description'])->not->toContain($sighting->description);
});

it('publishes Organization structured data on the home page', function () {
    $ld = jsonLd($this->get('/')->getContent());

    expect($ld)->toHaveCount(1)
        ->and($ld[0]['@type'])->toBe('Organization')
        ->and($ld[0]['address']['addressLocality'])->toBe('Lages');
});

it('publishes Product structured data on product pages', function () {
    publicContent();

    $ld = jsonLd($this->get('/loja/adesivo-ovniporto')->getContent());

    expect($ld[0]['@type'])->toBe('Product')
        ->and($ld[0]['offers']['priceCurrency'])->toBe('BRL');
});

it('publishes Article structured data on construction posts', function () {
    publicContent();

    $ld = jsonLd($this->get('/obra/a-primeira-pedra')->getContent());

    expect($ld[0]['@type'])->toBe('Article')
        ->and($ld[0]['headline'])->toBe('A primeira pedra')
        ->and($ld[0]['image'])->toStartWith('https://ovniporto.test/og/obra/a-primeira-pedra.');
});

it('publishes Place structured data with the runway coordinates', function () {
    $ld = jsonLd($this->get('/o-lugar')->getContent());

    expect($ld[0]['@type'])->toBe('Place')
        ->and($ld[0]['geo']['latitude'])->toBe((float) config('ovniporto.location.lat'))
        ->and($ld[0]['geo']['longitude'])->toBe((float) config('ovniporto.location.lng'));
});

it('publishes the FAQ as FAQPage with plain-text answers', function () {
    Faq::query()->create(['question' => 'O OVNIPORTO já existe?', 'answer' => 'Ainda **não**: é meta para 2028.', 'sort_order' => 0]);

    $ld = jsonLd($this->get('/faq')->getContent());

    expect($ld[0]['@type'])->toBe('FAQPage')
        ->and($ld[0]['mainEntity'][0]['name'])->toBe('O OVNIPORTO já existe?')
        ->and($ld[0]['mainEntity'][0]['acceptedAnswer']['text'])->toBe('Ainda não: é meta para 2028.');
});

it('cannot be broken out of the JSON-LD script by content', function () {
    Faq::query()->create(['question' => '</script><script>alert(1)</script>', 'answer' => 'x', 'sort_order' => 0]);

    $this->get('/faq')->assertDontSee('</script><script>alert(1)', false);
});

it('describes each Atlas case with its own title, photo and canonical URL', function () {
    $html = $this->get('/origem/atlas/cachi')->assertOk()->getContent();
    $tags = previewTags($html);

    expect($tags['title'])->toBe('Ovnipuerto de Cachi · Atlas dos Ovnipuertos · OVNIPORTO Lages')
        ->and($tags['description'])->toContain('Argentina')
        ->and($tags['og:image'])->toBe('https://ovniporto.test/origin/cachi-aereo.jpg')
        ->and($tags['canonical'])->toBe('https://ovniporto.test/origem/atlas/cachi')
        ->and($tags['robots'])->toBe('index, follow');
});

it('marks up only Atlas cases whose coordinates are confirmed as places', function () {
    $angelholm = collect(jsonLd($this->get('/origem/atlas/angelholm')->getContent()))->firstWhere('@type', 'Place');
    $cachi = collect(jsonLd($this->get('/origem/atlas/cachi')->getContent()))->firstWhere('@type', 'Place');

    expect($angelholm['geo']['latitude'])->toBe(56.231944)
        ->and($cachi)->toBeNull();
});

it('gives the origin pages their own preview and an article for Cachi', function () {
    expect(previewTags($this->get('/origem')->getContent())['og:image'])->toBe('https://ovniporto.test/origin/cachi-aereo.jpg')
        ->and(previewTags($this->get('/origem/atlas')->getContent())['title'])->toBe('Atlas Mundial dos Ovnipuertos · OVNIPORTO Lages')
        ->and(collect(jsonLd($this->get('/origem/cachi')->getContent())[0]['@graph'])->firstWhere('@type', 'Article')['headline'])->toBe('Ovnipuerto de Cachi: Werner Jaisli e a Estrella de la Esperanza');
});

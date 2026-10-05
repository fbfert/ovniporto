<?php

use App\Application\Content\UseCases\BuildSitemap;
use App\Domain\Origin\Contracts\OriginLibrary;
use App\Infrastructure\Content\SitemapFile;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    config(['app.url' => 'https://ovniporto.test', 'inertia.ssr.enabled' => false]);
    URL::forceRootUrl('https://ovniporto.test');
});

/** @return array<string, array<string, mixed>> graph nodes keyed by their first type */
function cachiGraph(): array
{
    $html = test()->get('/origem/cachi')->assertOk()->getContent();
    preg_match('#<script type="application/ld\+json">(.*?)</script>#s', $html, $match);
    $graph = json_decode($match[1], true)['@graph'];

    return collect($graph)->keyBy(fn (array $node) => (array) $node['@type'] === [] ? '' : ((array) $node['@type'])[0])->all();
}

it('names the place, Werner and the star in the title and description', function () {
    $html = $this->get('/origem/cachi')->getContent();
    preg_match_all('#<meta (?:name|property)="([^"]+)" content="([^"]*)"#', $html, $meta);
    preg_match('#<title[^>]*>(.*?)</title>#s', $html, $title);
    $tags = ['title' => html_entity_decode(trim($title[1])), ...array_map(html_entity_decode(...), array_combine($meta[1], $meta[2]))];

    expect($tags['title'])->toStartWith('Ovnipuerto de Cachi: Werner Jaisli e a Estrella de la Esperanza')
        ->and($tags['description'])->toContain('Ovnipuerto de Cachi', 'Werner', 'Estrella de la Esperanza')
        ->and(mb_strlen($tags['description']))->toBeLessThanOrEqual(160)
        ->and($tags['og:type'])->toBe('article');
});

it('sends the place name for the heading and the summary to the page', function () {
    $this->get('/origem/cachi')->assertInertia(fn (Assert $page) => $page
        ->where('dossier.reference.name', 'Ovnipuerto de Cachi')
        ->has('dossier.summary.items', 6)
        ->etc());
});

it('publishes the article, the place, Werner, the breadcrumb and the summary as a graph', function () {
    $graph = cachiGraph();
    $dossier = app(OriginLibrary::class)->cachi();
    $sources = collect($dossier['chapters'])->firstWhere('id', 'fontes')['sources'];

    expect($graph['Article']['citation'])->toHaveCount(count($sources))
        ->and(array_column($graph['Article']['citation'], 'url'))->toBe(array_column($sources, 'url'))
        ->and($graph['Article']['about'])->toBe([['@id' => 'https://ovniporto.test/origem/cachi#place'], ['@id' => 'https://ovniporto.test/origem/cachi#werner']])
        ->and($graph['Article']['dateModified'])->toBe($dossier['reference']['updatedAt'])
        ->and(array_column($graph['Article']['mentions'], 'name'))->not->toContain('Werner Jaisli')
        ->and($graph['Place']['alternateName'])->toContain('Estrella de la Esperanza')
        ->and($graph['Place'])->not->toHaveKey('geo')
        ->and($graph['Person']['alternateName'])->toContain('Terry Jaisli')
        ->and(array_column($graph['BreadcrumbList']['itemListElement'], 'item'))->toBe(['https://ovniporto.test', 'https://ovniporto.test/origem', 'https://ovniporto.test/origem/cachi'])
        ->and(array_column($graph['FAQPage']['mainEntity'], 'name'))->toBe(array_column($dossier['summary']['items'], 'question'))
        ->and(array_column(array_column($graph['FAQPage']['mainEntity'], 'acceptedAnswer'), 'text'))->toBe(array_column($dossier['summary']['items'], 'answer'));
});

it('never describes the Lages runway as open in the summary', function () {
    $answers = implode(' ', array_column(app(OriginLibrary::class)->cachi()['summary']['items'], 'answer'));

    expect($answers)->toContain('ainda não existe');
});

it('serves llms.txt with the dossier, its answers and its sources', function () {
    $sources = collect(app(OriginLibrary::class)->cachi()['chapters'])->firstWhere('id', 'fontes')['sources'];
    $response = $this->get('/llms.txt')->assertOk()->assertHeader('Content-Type', 'text/plain; charset=UTF-8');

    $response->assertSee('# OVNIPORTO Lages', false)
        ->assertSee('(https://ovniporto.test/origem/cachi)', false)
        ->assertSee('**Quem foi Werner Jaisli?**', false)
        ->assertSee('- 2008: A noite de 24 de novembro.', false)
        ->assertSee('[Atlas Mundial dos Ovnipuertos](https://ovniporto.test/origem/atlas)', false);
    foreach ($sources as $source) {
        $response->assertSee($source['url'], false);
    }
});

it('dates the Cachi dossier in the sitemap', function () {
    $path = sys_get_temp_dir().'/ovniporto-sitemap-'.uniqid().'.xml';
    $this->app->bind(SitemapFile::class, fn ($app) => new SitemapFile($app->make(BuildSitemap::class), $path));

    $xml = simplexml_load_string($this->get('/sitemap.xml')->streamedContent());
    $cachi = collect(iterator_to_array($xml->url, false))->first(fn ($url) => (string) $url->loc === 'https://ovniporto.test/origem/cachi');
    @unlink($path);

    expect((string) $cachi->lastmod)->toBe(app(OriginLibrary::class)->cachi()['reference']['updatedAt']);
});

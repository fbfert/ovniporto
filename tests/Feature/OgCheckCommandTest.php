<?php

use Database\Seeders\ProductSeeder;
use Illuminate\Support\Facades\Http;

beforeEach(fn () => config(['app.url' => 'https://ovniporto.test', 'inertia.ssr.enabled' => false]));

function blankJpeg(int $width, int $height): string
{
    ob_start();
    imagejpeg(imagecreatetruecolor($width, $height));

    return (string) ob_get_clean();
}

it('prints the preview tags of a local page and checks its image', function () {
    $this->seed(ProductSeeder::class);
    $html = $this->get('/loja')->getContent();
    Http::fake([
        'ovniporto.test/loja' => Http::response($html),
        'ovniporto.test/og/*' => Http::response(blankJpeg(1200, 630), 200, ['Content-Type' => 'image/jpeg']),
    ]);

    $this->artisan('og:check', ['url' => 'https://ovniporto.test/loja'])
        ->expectsTable(['Tag', 'Conteúdo'], [
            ['title', 'Loja · OVNIPORTO Lages'],
            ['description', 'Adesivo, camiseta, caneca e Kit Abdução do OVNIPORTO. Parte de cada venda vira pista.'],
            ['canonical', 'https://ovniporto.test/loja'],
            ['robots', 'index, follow'],
            ['og:title', 'Loja · OVNIPORTO Lages'],
            ['og:description', 'Adesivo, camiseta, caneca e Kit Abdução do OVNIPORTO. Parte de cada venda vira pista.'],
            ['og:url', 'https://ovniporto.test/loja'],
            ['og:image', 'https://ovniporto.test/og/default.jpg'],
            ['og:type', 'website'],
            ['twitter:card', 'summary_large_image'],
        ])
        ->expectsOutput('Imagem ok: 1200×630.')
        ->assertSuccessful();
});

it('fails when the page has no preview image', function () {
    Http::fake(['example.test/*' => Http::response('<html><head><title>Sem prévia</title></head></html>')]);

    $this->artisan('og:check', ['url' => 'https://example.test/x'])
        ->expectsOutputToContain('Faltando: og:title, og:description, og:image')
        ->assertFailed();
});

it('fails when the preview image does not open', function () {
    $html = $this->get('/lenda')->getContent();
    Http::fake([
        'ovniporto.test/lenda' => Http::response($html),
        'ovniporto.test/og/*' => Http::response('', 404),
    ]);

    $this->artisan('og:check', ['url' => 'https://ovniporto.test/lenda'])
        ->expectsOutputToContain('A imagem não abre')
        ->assertFailed();
});

it('warns about an image off the 1200×630 format', function () {
    $html = $this->get('/lenda')->getContent();
    Http::fake([
        'ovniporto.test/lenda' => Http::response($html),
        'ovniporto.test/og/*' => Http::response(blankJpeg(800, 800)),
    ]);

    $this->artisan('og:check', ['url' => 'https://ovniporto.test/lenda'])
        ->expectsOutputToContain('800×800')
        ->assertSuccessful();
});

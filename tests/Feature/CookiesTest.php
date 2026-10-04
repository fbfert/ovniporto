<?php

use Illuminate\Support\Facades\Mail;
use Illuminate\Testing\TestResponse;

beforeEach(fn () => config(['inertia.ssr.enabled' => false]));

/** @return list<string> */
function cookieNames(TestResponse $response): array
{
    return array_map(fn ($cookie) => $cookie->getName(), $response->headers->getCookies());
}

it('gives a new visitor of the home only the session and the XSRF cookies', function () {
    $response = $this->get('/')->assertOk();

    expect(cookieNames($response))->toEqualCanonicalizing([config('session.cookie'), 'XSRF-TOKEN']);
});

it('keeps it that way on every public page', function (string $path) {
    expect(cookieNames($this->get($path)))->toEqualCanonicalizing([config('session.cookie'), 'XSRF-TOKEN']);
})->with(['/loja', '/mapa', '/regiao', '/o-lugar', '/postal', '/privacidade']);

it('marks the session cookie HttpOnly and SameSite=Lax', function () {
    $session = collect($this->get('/')->headers->getCookies())->first(fn ($c) => $c->getName() === config('session.cookie'));

    expect($session->isHttpOnly())->toBeTrue()
        ->and(strtolower((string) $session->getSameSite()))->toBe('lax');
});

it('loads no script or stylesheet from a third party besides Google Fonts', function () {
    Mail::fake();
    app()->detectEnvironment(fn () => 'production');
    config(['services.umami.script_url' => 'https://metrica.ovniporto.tars.art.br/script.js', 'services.umami.website_id' => 'x']);

    $html = $this->get('/')->getContent();
    preg_match_all('#<(?:script|link)[^>]+(?:src|href)="(https?://[^"]+)"#', $html, $matches);
    $hosts = array_unique(array_map(fn (string $url) => parse_url($url, PHP_URL_HOST), $matches[1]));

    expect(array_diff($hosts, [parse_url((string) config('app.url'), PHP_URL_HOST), 'fonts.googleapis.com', 'fonts.gstatic.com', 'metrica.ovniporto.tars.art.br']))->toBe([]);
});

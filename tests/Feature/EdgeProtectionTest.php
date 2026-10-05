<?php

use App\Models\Member;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;

beforeEach(fn () => config(['inertia.ssr.enabled' => false]));

function policyOf(TestResponse $response): array
{
    $header = (string) ($response->headers->get('Content-Security-Policy') ?? $response->headers->get('Content-Security-Policy-Report-Only'));

    return collect(explode(';', $header))->mapWithKeys(function (string $directive) {
        $parts = preg_split('/\s+/', trim($directive)) ?: [];

        return [array_shift($parts) => $parts];
    })->all();
}

it('enforces a policy that allows only PayPal, the map tiles and our own metric', function () {
    config(['ovniporto.csp' => 'enforce', 'services.umami.script_url' => 'https://metrica.ovniporto.tars.art.br/script.js']);

    $response = $this->get('/');
    $policy = policyOf($response);

    expect($response->headers->has('Content-Security-Policy'))->toBeTrue()
        ->and($policy['script-src'])->toEqual(["'self'", 'https://*.paypal.com', 'https://*.paypalobjects.com', 'https://metrica.ovniporto.tars.art.br'])
        ->and($policy['script-src'])->not->toContain("'unsafe-inline'")
        ->and($policy['img-src'])->toContain('https://tile.openstreetmap.org')
        ->and($policy['frame-src'])->toContain('https://*.paypal.com')->toContain('https://sketchfab.com')
        ->and($policy['connect-src'])->toContain('https://metrica.ovniporto.tars.art.br')
        ->and($policy['frame-ancestors'])->toBe(["'none'"])
        ->and($policy['object-src'])->toBe(["'none'"]);
});

it('only reports on staging, and stays off in local development', function () {
    config(['ovniporto.csp' => 'report-only']);
    $staging = $this->get('/');
    expect($staging->headers->has('Content-Security-Policy-Report-Only'))->toBeTrue()
        ->and($staging->headers->has('Content-Security-Policy'))->toBeFalse();

    config(['ovniporto.csp' => 'off']);
    expect($this->get('/')->headers->has('Content-Security-Policy'))->toBeFalse();
});

it('sends frame, type, referrer and permission protections everywhere', function () {
    $response = $this->get('/loja');

    expect($response->headers->get('X-Frame-Options'))->toBe('DENY')
        ->and($response->headers->get('X-Content-Type-Options'))->toBe('nosniff')
        ->and($response->headers->get('Referrer-Policy'))->toBe('strict-origin-when-cross-origin')
        ->and($response->headers->get('Permissions-Policy'))->toContain('camera=()');
});

it('sends HSTS over HTTPS, as seen through the reverse proxy', function () {
    $proxied = $this->withServerVariables(['REMOTE_ADDR' => '172.18.0.5'])
        ->withHeaders(['X-Forwarded-Proto' => 'https', 'X-Forwarded-Host' => 'ovniporto.tars.art.br', 'X-Forwarded-Port' => '443'])
        ->get('/');

    expect($proxied->headers->get('Strict-Transport-Security'))->toBe('max-age=31536000; includeSubDomains');
});

it('sends no HSTS over plain HTTP', function () {
    expect($this->get('http://localhost/')->headers->has('Strict-Transport-Security'))->toBeFalse();
});

it('does not trust forwarded headers from outside the private network', function () {
    $spoofed = $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.9'])->withHeaders(['X-Forwarded-Proto' => 'https'])->get('/');

    expect($spoofed->headers->has('Strict-Transport-Security'))->toBeFalse();
});

it('relaxes the policy only for the admin queue and health tools', function () {
    config(['ovniporto.csp' => 'enforce']);
    $this->actingAs(Member::factory()->role('admin')->create());

    expect(policyOf($this->get('/painel/filas'))['script-src'])->toContain("'unsafe-inline'")
        ->and(policyOf($this->get('/painel'))['script-src'])->not->toContain("'unsafe-inline'");
});

it('logs CSP violation reports without storing them', function () {
    Log::spy();

    $this->postJson('/csp-report', ['csp-report' => [
        'violated-directive' => 'script-src', 'blocked-uri' => 'https://evil.test/x.js', 'document-uri' => 'https://ovniporto.tars.art.br/loja',
    ]])->assertNoContent();

    Log::shouldHaveReceived('warning')->withArgs(fn ($message, $context) => $message === 'CSP violation' && $context['blocked'] === 'https://evil.test/x.js');
});

it('answers 429 to a client sending too many reports', function () {
    Storage::fake('local');
    $member = Member::factory()->create(['nickname' => 'coruja']);
    $payload = [
        'type' => 'light', 'description' => 'Uma luz verde parada sobre a serra, depois sumiu de uma vez.',
        'observedDate' => now()->toDateString(), 'timeRange' => 'night', 'lat' => -27.85, 'lng' => -50.22,
        'gaze' => 'NE', 'nickname' => 'coruja', 'consent' => '1', 'photos' => [],
    ];

    for ($i = 0; $i < 5; $i++) {
        $this->actingAs($member)->post('/relatar', $payload)->assertStatus(302);
    }
    $this->actingAs($member)->post('/relatar', $payload)->assertStatus(429);
});

it('limits sign-in attempts, checkout and the public API', function (string $method, string $path, int $limit) {
    for ($i = 0; $i < $limit; $i++) {
        $this->call($method, $path);
    }

    $this->call($method, $path)->assertStatus(429);
})->with([
    'sign-in' => ['GET', '/auth/google', 20],
    'checkout' => ['POST', '/checkout', 10],
    'API' => ['GET', '/api/sightings', 60],
]);

it('lets the origin videos play after a click, from the no-cookie player only', function () {
    config(['ovniporto.csp' => 'enforce']);

    foreach (['/', '/origem/cachi'] as $path) {
        $frames = policyOf($this->get($path))['frame-src'];

        expect($frames)->toContain('https://www.youtube-nocookie.com')
            ->not->toContain('https://www.youtube.com')
            ->not->toContain('https://*.youtube.com');
    }
});

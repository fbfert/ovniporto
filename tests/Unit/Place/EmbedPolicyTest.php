<?php

use App\Domain\Place\EmbedPolicy;

const HOSTS = ['sketchfab.com', 'kuula.co'];

it('accepts a link or an iframe snippet from an allowed host', function (string $input, string $src) {
    expect(EmbedPolicy::src($input, HOSTS))->toBe($src);
})->with([
    'link' => ['https://sketchfab.com/models/abc/embed', 'https://sketchfab.com/models/abc/embed'],
    'subdomain' => ['https://www.kuula.co/share/collection/7abc', 'https://www.kuula.co/share/collection/7abc'],
    'iframe' => ['<iframe width="640" src="https://sketchfab.com/models/abc/embed?autostart=1&amp;ui=0"></iframe>', 'https://sketchfab.com/models/abc/embed?autostart=1&ui=0'],
]);

it('refuses other hosts, look-alikes and plain http', function (string $input) {
    expect(fn () => EmbedPolicy::src($input, HOSTS))->toThrow(InvalidArgumentException::class);
})->with([
    'other host' => ['https://evil.example.com/3d'],
    'look-alike' => ['https://sketchfab.com.evil.io/models/abc'],
    'suffix trick' => ['https://notsketchfab.com/models/abc'],
    'http' => ['http://sketchfab.com/models/abc/embed'],
    'javascript' => ['javascript:alert(1)'],
]);

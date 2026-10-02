<?php

use App\Application\Content\UseCases\GetCommunityLinks;

it('returns the filled links and null for the empty ones', function () {
    $links = (new GetCommunityLinks(contentRepository([
        'link_whatsapp' => 'https://chat.whatsapp.com/abc',
        'link_instagram' => '  ',
        'contact_email' => 'oi@ovniporto.test',
    ])))->execute();

    expect($links)->toBe([
        'whatsapp' => 'https://chat.whatsapp.com/abc',
        'instagram' => null,
        'email' => 'oi@ovniporto.test',
    ]);
});

it('falls back to the default contact e-mail', function () {
    $links = (new GetCommunityLinks(contentRepository([])))->execute();

    expect($links['email'])->toBe(GetCommunityLinks::FALLBACK_EMAIL);
});

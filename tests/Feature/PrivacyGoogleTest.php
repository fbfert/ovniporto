<?php

use App\Domain\Members\Data\Identity;
use Illuminate\Support\Facades\Schema;

it('keeps from Google only the id, name, e-mail and avatar', function () {
    $fields = array_map(fn (ReflectionParameter $p) => $p->getName(), (new ReflectionMethod(Identity::class, '__construct'))->getParameters());

    expect($fields)->toBe(['providerId', 'name', 'email', 'avatarUrl']);
});

it('has no place to store Google tokens or any other account data', function () {
    expect(Schema::getColumnListing('members'))->toEqualCanonicalizing([
        'id', 'google_id', 'name', 'email', 'avatar_url',
        'nickname', 'city', 'role', 'terms_accepted_at', 'blocked_at', 'blocked_reason',
        'created_at', 'updated_at',
    ]);
});

it('never asks Google for offline access or extra scopes', function () {
    config(['services.google' => ['client_id' => 'id', 'client_secret' => 'secret', 'redirect' => 'http://localhost/auth/google/callback']]);

    $url = (string) $this->get('/auth/google')->assertRedirect()->headers->get('Location');
    parse_str((string) parse_url($url, PHP_URL_QUERY), $query);

    expect(explode(' ', $query['scope']))->toEqualCanonicalizing(['openid', 'email', 'profile'])
        ->and($query)->not->toHaveKey('access_type')
        ->and($query)->not->toHaveKey('include_granted_scopes');
});

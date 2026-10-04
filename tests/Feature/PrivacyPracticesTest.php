<?php

use App\Infrastructure\Identity\GoogleIdentityProvider;
use App\Infrastructure\Sightings\SightingPhotoUrls;
use Inertia\Testing\AssertableInertia as Assert;

it('shows "O que fazemos na prática" on /privacidade, with the numbers the code enforces', function () {
    config(['privacy.fiscal_retention_years' => 5, 'privacy.access_log_months' => 6]);

    $this->get('/privacidade')->assertOk()->assertInertia(fn (Assert $page) => $page
        ->where('practices.title', 'O que fazemos na prática')
        ->where('toc.0', ['id' => 'o-que-fazemos-na-pratica', 'title' => 'O que fazemos na prática'])
        ->where('practices.items', function ($items) {
            $text = implode("\n", collect($items)->all());

            return count($items) === 10
                && str_contains($text, SightingPhotoUrls::SIGNED_MINUTES.' minutos')
                && str_contains($text, implode(', ', GoogleIdentityProvider::SCOPES))
                && str_contains($text, 'por 5 anos após o pagamento')
                && str_contains($text, 'no máximo 6 meses')
                && str_contains($text, 'cookies de terceiros')
                && ! str_contains($text, ':');
        })
    );
});

it('follows a change in the retention term', function () {
    config(['privacy.fiscal_retention_years' => 6]);

    $this->get('/privacidade')->assertInertia(fn (Assert $page) => $page
        ->where('practices.items', fn ($items) => str_contains(implode(' ', collect($items)->all()), 'por 6 anos'))
    );
});

it('leaves the terms page without the practices', function () {
    $this->get('/termos')->assertInertia(fn (Assert $page) => $page->where('practices', null));
});

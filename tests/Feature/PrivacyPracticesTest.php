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

            return count($items) === 14
                && str_contains($text, SightingPhotoUrls::SIGNED_MINUTES.' minutos')
                && str_contains($text, implode(', ', GoogleIdentityProvider::SCOPES))
                && str_contains($text, 'por 5 anos após o pagamento')
                && str_contains($text, 'também ficam 5 anos após o pagamento')
                && str_contains($text, 'Conta que fica 5 anos sem entrar é anonimizada')
                && str_contains($text, 'Avisamos por e-mail 30 dias antes')
                && str_contains($text, 'no máximo 6 meses')
                && str_contains($text, 'cookies de terceiros')
                && str_contains($text, '16 anos ou mais')
                && str_contains($text, 'link para sair')
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

it('follows a change in the inactivity term and notice', function () {
    config(['privacy.inactive_account_years' => 3, 'privacy.inactivity_notice_days' => 45]);

    $this->get('/privacidade')->assertInertia(fn (Assert $page) => $page
        ->where('practices.items', function ($items) {
            $text = implode(' ', collect($items)->all());

            return str_contains($text, 'Conta que fica 3 anos sem entrar') && str_contains($text, '45 dias antes');
        })
    );
});

it('leaves the terms page without the practices', function () {
    $this->get('/termos')->assertInertia(fn (Assert $page) => $page->where('practices', null));
});

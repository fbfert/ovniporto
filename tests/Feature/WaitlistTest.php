<?php

use App\Mail\ConfirmWaitlistMail;
use App\Models\NewsletterSubscriber;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

beforeEach(fn () => Mail::fake());

it('stores a pending subscription with consent and queues the confirmation', function () {
    $this->from('/')->post('/avise-me', ['email' => 'vigia@serra.com', 'consent' => '1'])
        ->assertRedirect('/')
        ->assertSessionHas('toast', 'Quase lá: confirme no seu e-mail.');

    $subscriber = NewsletterSubscriber::query()->sole();
    expect($subscriber->email)->toBe('vigia@serra.com')
        ->and($subscriber->consented_at)->not->toBeNull()
        ->and($subscriber->consent_text)->not->toBeEmpty()
        ->and($subscriber->confirmed_at)->toBeNull();

    Mail::assertQueued(ConfirmWaitlistMail::class, fn ($mail) => $mail->hasTo('vigia@serra.com'));
});

it('rejects an invalid e-mail with a Portuguese message', function () {
    $this->from('/')->post('/avise-me', ['email' => 'abc', 'consent' => '1'])
        ->assertSessionHasErrors(['email' => 'Esse e-mail não parece certo.']);

    expect(NewsletterSubscriber::query()->count())->toBe(0);
});

it('requires explicit consent', function () {
    $this->from('/')->post('/avise-me', ['email' => 'vigia@serra.com'])
        ->assertSessionHasErrors('consent');

    expect(NewsletterSubscriber::query()->count())->toBe(0);
});

it('answers a repeated e-mail exactly like a new one, without duplicating', function () {
    $first = $this->from('/')->post('/avise-me', ['email' => 'vigia@serra.com', 'consent' => '1']);
    $second = $this->from('/')->post('/avise-me', ['email' => 'VIGIA@serra.com', 'consent' => '1']);

    expect($second->getStatusCode())->toBe($first->getStatusCode())
        ->and($second->getSession()->get('toast'))->toBe($first->getSession()->get('toast'))
        ->and(NewsletterSubscriber::query()->count())->toBe(1);
    Mail::assertQueuedCount(1);
});

it('confirms through the signed link', function () {
    $subscriber = NewsletterSubscriber::query()->create([
        'email' => 'vigia@serra.com', 'consent_text' => 'ok', 'consented_at' => now(),
    ]);

    $this->get(URL::temporarySignedRoute('waitlist.confirm', now()->addDay(), ['subscriber' => $subscriber->id]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Waitlist/Confirmed'));

    expect($subscriber->fresh()->confirmed_at)->not->toBeNull();
});

it('refuses a tampered confirmation link', function () {
    $subscriber = NewsletterSubscriber::query()->create([
        'email' => 'vigia@serra.com', 'consent_text' => 'ok', 'consented_at' => now(),
    ]);
    $url = URL::temporarySignedRoute('waitlist.confirm', now()->addDay(), ['subscriber' => $subscriber->id]);

    $this->get($url.'x')->assertForbidden();

    expect($subscriber->fresh()->confirmed_at)->toBeNull();
});

it('rate limits the form per IP', function () {
    foreach (range(1, 5) as $i) {
        $this->post('/avise-me', ['email' => "v{$i}@serra.com", 'consent' => '1'])->assertRedirect();
    }

    $this->post('/avise-me', ['email' => 'v6@serra.com', 'consent' => '1'])->assertStatus(429);
});

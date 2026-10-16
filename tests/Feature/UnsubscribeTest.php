<?php

use App\Domain\Privacy\MailingList;
use App\Infrastructure\Mail\UnsubscribeLinks;
use App\Models\Consent;
use App\Models\NewsletterSubscriber;
use App\Models\ResearchCollaborator;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    Mail::fake();
    $this->from('/')->post('/avise-me', ['email' => 'vigia@serra.com', 'consent' => '1']);
    $this->subscriber = NewsletterSubscriber::query()->sole();
    $this->link = UnsubscribeLinks::to(MailingList::Waitlist, $this->subscriber->id);
});

it('shows what will be canceled, with the e-mail masked, and removes nothing on opening', function () {
    $this->get($this->link)->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('Privacy/Unsubscribe')
        ->where('list', 'avise-me')
        ->where('email', 'v***@serra.com')
        ->where('done', false)
    );

    expect(NewsletterSubscriber::query()->count())->toBe(1);
});

it('cancels on confirmation, withdraws the consent and says so', function () {
    $this->post($this->link)->assertRedirect($this->link);
    $this->get($this->link)->assertInertia(fn (Assert $page) => $page->where('done', true)->where('email', null));

    $consent = Consent::query()->where('type', 'newsletter')->sole();
    expect(NewsletterSubscriber::query()->count())->toBe(0)
        ->and($consent->withdrawn_at)->not->toBeNull()
        ->and($consent->subject)->toBe("avise-me:{$this->subscriber->id}");
});

it('says there is nothing to cancel on a second visit', function () {
    $this->post($this->link);
    $this->flushSession();

    $this->get($this->link)->assertOk()->assertInertia(fn (Assert $page) => $page->where('email', null)->where('done', false));
});

it('refuses a link whose id was changed', function () {
    $other = str_replace("/avise-me/{$this->subscriber->id}?", '/avise-me/'.($this->subscriber->id + 1).'?', $this->link);

    $this->get($other)->assertForbidden();
    $this->post($other)->assertForbidden();
    expect(NewsletterSubscriber::query()->count())->toBe(1);
});

it('cancels an offer of help to the origin research', function () {
    $collaborator = ResearchCollaborator::query()->create([
        'name' => 'Ana', 'email' => 'ana@serra.com', 'location' => 'Lages', 'areas' => ['pesquisa'],
        'message' => 'Tenho fotos.', 'consent_text' => 'sim', 'consented_at' => now(),
    ]);
    $link = UnsubscribeLinks::to(MailingList::Collaboration, $collaborator->id);

    $this->get($link)->assertInertia(fn (Assert $page) => $page->where('list', 'colaboracao')->where('email', 'a***@serra.com'));
    $this->post($link)->assertRedirect($link);

    expect(ResearchCollaborator::query()->count())->toBe(0);
});

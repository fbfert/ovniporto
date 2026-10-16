<?php

use App\Domain\Members\Contracts\IdentityProvider;
use App\Domain\Members\Contracts\MemberRepository;
use App\Domain\Members\Data\Identity;
use App\Domain\Orders\Contracts\OrderRepository;
use App\Domain\Sightings\Contracts\MemberSightingRepository;
use App\Domain\Sightings\SightingStatus;
use App\Mail\InactiveStaffAlertMail;
use App\Mail\InactivityNoticeMail;
use App\Models\Consent;
use App\Models\Member;
use App\Models\NewsletterSubscriber;
use App\Models\Order;
use App\Models\Sighting;
use App\Models\SightingUpload;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Support\Orders;

beforeEach(function () {
    Mail::fake();
    Storage::fake('local');
    Storage::fake('public');
});

/** Google answers the callback with this identity. */
function googleSignsIn(Identity $identity): void
{
    app()->instance(IdentityProvider::class, new class($identity) implements IdentityProvider
    {
        public function __construct(private Identity $identity) {}

        public function redirectUrl(): string
        {
            return 'https://accounts.google.test/consent';
        }

        public function identity(): Identity
        {
            return $this->identity;
        }
    });
}

/** A member last seen on 2026-03-01 03:00, noticed by the routine of 2031-01-30. */
function noticedMember(array $attributes = []): Member
{
    return Member::factory()->lastSeen(Carbon::parse('2026-03-01 03:00'))->create([
        'inactivity_notice_sent_at' => Carbon::parse('2031-01-30 03:15'),
        ...$attributes,
    ]);
}

/** Counts the writes of the last access while $requests run. */
function lastSeenWrites(callable $requests): int
{
    $writes = 0;
    DB::listen(function ($query) use (&$writes) {
        if (str_starts_with(strtolower($query->sql), 'update') && str_contains($query->sql, 'last_seen_at')) {
            $writes++;
        }
    });
    $requests();

    return $writes;
}

it('writes the last access once a day while the session stays open', function () {
    $member = Member::factory()->create();
    $this->actingAs($member);

    $sameDay = lastSeenWrites(function () {
        $this->get('/conta')->assertOk();
        $this->get('/conta')->assertOk();
    });
    $this->travel(1)->days();
    $nextDay = lastSeenWrites(fn () => $this->get('/conta')->assertOk());

    expect($sameDay)->toBe(1)
        ->and($nextDay)->toBe(1)
        ->and($member->fresh()->last_seen_at->isToday())->toBeTrue();
});

it('records the sign-in as the last access and clears a pending notice', function () {
    $member = noticedMember(['google_id' => 'g-ana']);
    googleSignsIn(new Identity('g-ana', 'Ana', $member->email, null));
    $this->travelTo(Carbon::parse('2031-02-10 12:00'));

    $writes = lastSeenWrites(function () {
        $this->get('/auth/google/callback')->assertRedirect('/conta');
        $this->get('/conta')->assertOk();
    });

    $member->refresh();
    expect($writes)->toBe(1)
        ->and($member->last_seen_at->toDateTimeString())->toBe('2031-02-10 12:00:00')
        ->and($member->inactivity_notice_sent_at)->toBeNull();
});

it('deletes the unpublished reports with their files and keeps the published one', function () {
    $member = Member::factory()->create();
    $published = Sighting::factory()->approved()->create(['member_id' => $member->id, 'public_nickname' => 'coruja']);
    $pending = Sighting::factory()->create(['member_id' => $member->id]);
    $rejected = Sighting::factory()->create(['member_id' => $member->id, 'status' => SightingStatus::Rejected]);
    $photo = $pending->photos()->create(['path' => "sightings/{$pending->id}/1", 'width' => 800, 'height' => 600, 'sort_order' => 0]);
    $photo->update(['variants' => [400], 'processed_at' => now()]);
    Storage::disk('local')->put("sightings/{$pending->id}/1-400.webp", 'webp');
    Storage::disk('public')->put("sightings/{$published->id}/1-400.webp", 'webp');
    Storage::disk('local')->put("uploads/{$member->id}/solta.jpg", 'jpg');
    SightingUpload::query()->create(['member_id' => $member->id, 'path' => "uploads/{$member->id}/solta.jpg", 'mime' => 'image/jpeg', 'size' => 3]);

    app(MemberSightingRepository::class)->deleteUnpublishedOf($member->id);

    expect(Sighting::query()->pluck('id')->all())->toBe([$published->id])
        ->and(Sighting::query()->find($rejected->id))->toBeNull()
        ->and(Storage::disk('local')->allFiles())->toBe([])
        ->and(Storage::disk('public')->allFiles())->toBe(["sightings/{$published->id}/1-400.webp"])
        ->and(SightingUpload::query()->count())->toBe(0);
});

it('finds members by inactivity and anonymizes one without touching the nickname', function () {
    $repository = app(MemberRepository::class);
    $old = Member::factory()->lastSeen(Carbon::parse('2026-01-01'))->create(['nickname' => 'coruja', 'avatar_url' => 'https://lh3.test/a.jpg']);
    $recent = Member::factory()->lastSeen(Carbon::parse('2030-01-01'))->create();
    $staff = Member::factory()->role('moderator')->lastSeen(Carbon::parse('2026-01-01'))->create();
    $cutoff = Carbon::parse('2027-01-01');

    expect(collect($repository->dueForInactivityNotice($cutoff, 10))->pluck('id')->all())->toEqualCanonicalizing([$old->id, $staff->id])
        ->and($repository->dueForAnonymization($cutoff, $cutoff, 10))->toBe([])
        ->and(collect($repository->inactiveStaffToAlert($cutoff, 10))->pluck('id')->all())->toBe([$staff->id]);

    $repository->markInactivityNoticeSent($old->id, Carbon::parse('2026-12-01'));
    expect($repository->dueForAnonymization($cutoff, $cutoff, 10))->toBe([['id' => $old->id, 'email' => $old->email]]);

    $repository->anonymize($old->id, Carbon::parse('2027-01-01'));
    $old->refresh();

    expect($old->only(['google_id', 'name', 'email', 'avatar_url', 'city', 'nickname']))->toBe([
        'google_id' => null, 'name' => null, 'email' => null, 'avatar_url' => null, 'city' => null, 'nickname' => 'coruja',
    ])->and($old->anonymized_at->toDateString())->toBe('2027-01-01')
        ->and(collect($repository->dueForInactivityNotice($cutoff, 10))->pluck('id')->all())->toBe([$staff->id])
        ->and($recent->fresh()->anonymized_at)->toBeNull();
});

it('renders the notice and the staff alert in Portuguese', function () {
    $notice = (new InactivityNoticeMail('coruja', '1 de março de 2031', 'https://ovniporto.test/entrar'))->render();
    $alert = (new InactiveStaffAlertMail('vigia', 'Moderação', '1 de março de 2026', 'https://ovniporto.test/painel/membros/3'))->render();

    expect($notice)->toContain('1 de março de 2031')->toContain('Entrar e manter a conta')->toContain('continuam no Livro')
        ->and($alert)->toContain('@vigia')->toContain('Moderação')->toContain('nunca são anonimizadas')
        ->and($notice.$alert)->not->toContain('lenda');
});

it('anonymizes through the daily command, keeping the published report public', function () {
    $member = noticedMember(['nickname' => 'coruja', 'email' => 'coruja@serra.test']);
    $published = Sighting::factory()->approved()->create(['member_id' => $member->id, 'public_nickname' => 'coruja']);
    $pending = Sighting::factory()->create(['member_id' => $member->id]);
    NewsletterSubscriber::query()->create(['email' => 'coruja@serra.test', 'source' => 'home', 'consent_text' => 'sim', 'consented_at' => now()]);
    Consent::query()->update(['ip_address' => '203.0.113.7', 'user_agent' => 'VigiaBrowser/1.0']);
    $this->travelTo(Carbon::parse('2031-03-01 03:15'));

    $this->artisan('members:anonymize-inactive')->expectsOutputToContain('1 conta(s) anonimizada(s)')->assertSuccessful();

    expect($member->fresh()->anonymized_at)->not->toBeNull()
        ->and(Sighting::query()->find($pending->id))->toBeNull()
        ->and(NewsletterSubscriber::query()->count())->toBe(0);
    Consent::query()->get()->each(fn (Consent $c) => expect($c->only(['member_id', 'email', 'ip_address', 'user_agent']))
        ->toBe(['member_id' => null, 'email' => null, 'ip_address' => null, 'user_agent' => null]));
    $this->get("/relatos/{$published->id}")->assertOk()->assertInertia(fn (Assert $page) => $page
        ->where('sighting.nickname', 'coruja')
        ->etc()
    );

    $this->artisan('members:anonymize-inactive')->expectsOutputToContain('0 conta(s) anonimizada(s)')->assertSuccessful();
    Mail::assertNothingQueued();
});

it('sends the notice and alerts only the admins about a panel account', function () {
    $admin = Member::factory()->role('admin')->create(['email' => 'torre@serra.test']);
    $member = Member::factory()->lastSeen(Carbon::parse('2026-03-01 03:00'))->create(['email' => 'ana@serra.test']);
    $moderator = Member::factory()->role('moderator')->lastSeen(Carbon::parse('2026-03-01 03:00'))->create();
    config(['ovniporto.alerts_email' => 'alertas@serra.test']);

    $this->travelTo(Carbon::parse('2031-01-30 03:15'));
    $this->artisan('members:anonymize-inactive')->assertSuccessful();
    Mail::assertQueued(InactivityNoticeMail::class, fn (InactivityNoticeMail $mail) => $mail->hasTo('ana@serra.test') && $mail->anonymizeOn === '1 de março de 2031');

    $this->travelTo(Carbon::parse('2031-03-01 03:15'));
    $this->artisan('members:anonymize-inactive')->assertSuccessful();
    $this->artisan('members:anonymize-inactive')->assertSuccessful();

    Mail::assertQueued(InactiveStaffAlertMail::class, 1);
    Mail::assertQueued(InactiveStaffAlertMail::class, fn (InactiveStaffAlertMail $mail) => $mail->hasTo('torre@serra.test')
        && ! $mail->hasTo('alertas@serra.test')
        && str_ends_with($mail->url, "/painel/membros/{$moderator->id}"));
    expect($moderator->fresh()->anonymized_at)->toBeNull()
        ->and($member->fresh()->anonymized_at)->not->toBeNull()
        ->and($admin->fresh()->anonymized_at)->toBeNull();
});

it('runs the inactivity cycle every day before the orders routine', function () {
    $events = collect(app(Schedule::class)->events())
        ->filter(fn ($event) => str_contains((string) $event->command, 'members:anonymize-inactive'));

    expect($events)->toHaveCount(1)
        ->and($events->first()->expression)->toBe('15 3 * * *');
});

it('creates a new account when the Google of an anonymized account signs in again', function () {
    $member = noticedMember(['google_id' => 'g-ana', 'email' => 'ana@serra.test']);
    $this->travelTo(Carbon::parse('2031-03-01 03:15'));
    $this->artisan('members:anonymize-inactive')->assertSuccessful();

    googleSignsIn(new Identity('g-ana', 'Ana', 'ana@serra.test', null));
    $this->get('/auth/google/callback')->assertRedirect('/boas-vindas');

    $new = Member::query()->where('google_id', 'g-ana')->sole();
    expect($new->id)->not->toBe($member->id)
        ->and($new->nickname)->toBeNull()
        ->and($member->fresh()->anonymized_at)->not->toBeNull();
});

it('anonymizes a paid order once the tax retention ends, and only once', function () {
    $guest = Orders::paidBy(null, 'visitante@serra.test', Carbon::parse('2026-03-01 10:00'));
    $active = Orders::paidBy(Member::factory()->create(), 'ana@serra.test', Carbon::parse('2027-03-01 10:00'));
    $deleted = Orders::paidBy(null, 'excluida@serra.test', Carbon::parse('2026-03-01 10:00'));
    $deleted->update(['customer_name' => 'Titular excluído', 'retention_until' => Carbon::parse('2031-03-01 10:00')]);
    $repository = app(OrderRepository::class);

    $first = $repository->anonymizePaidBefore(Carbon::parse('2026-03-01 10:00'));
    $second = $repository->anonymizePaidBefore(Carbon::parse('2026-03-01 10:00'));

    $guest->refresh();
    expect($first)->toBe(1)
        ->and($second)->toBe(0)
        ->and($guest->customer_name)->toBe('Titular anonimizado')
        ->and($guest->only(['customer_email', 'customer_phone', 'customer_cpf', 'member_id', 'retention_until']))
        ->toBe(['customer_email' => null, 'customer_phone' => null, 'customer_cpf' => null, 'member_id' => null, 'retention_until' => null])
        ->and($guest->address)->toBe(['city' => 'Lages', 'state' => 'SC'])
        ->and($guest->number)->not->toBeEmpty()
        ->and($guest->total_cents)->toBe(2390)
        ->and($guest->items()->count())->toBe(1)
        ->and($guest->anonymized_at)->not->toBeNull()
        ->and($active->fresh()->customer_name)->not->toBe('Titular anonimizado')
        ->and($deleted->fresh()->customer_cpf)->toBe('529.982.247-25');
});

it('anonymizes and purges in the orders routine', function () {
    $guest = Orders::paidBy(null, 'visitante@serra.test', Carbon::parse('2026-03-01 10:00'));
    $deleted = Orders::paidBy(null, 'excluida@serra.test', Carbon::parse('2026-03-01 10:00'));
    $deleted->update(['customer_name' => 'Titular excluído', 'retention_until' => Carbon::parse('2031-03-01 10:00')]);
    $this->travelTo(Carbon::parse('2031-03-01 10:00'));

    $this->artisan('privacy:purge-orders')
        ->expectsOutputToContain('1 pedido(s) anonimizado(s) e 1 apagado(s)')
        ->assertSuccessful();

    expect(Order::query()->find($deleted->id))->toBeNull()
        ->and($guest->fresh()->customer_name)->toBe('Titular anonimizado');
});

it('shows an anonymized order in the panel without customer data', function () {
    $store = Member::factory()->role('store')->create();
    $order = Orders::paidBy(null, 'visitante@serra.test', Carbon::parse('2026-03-01 10:00'));
    app(OrderRepository::class)->anonymizePaidBefore(Carbon::parse('2026-03-01 10:00'));

    $this->actingAs($store)->get("/painel/pedidos/{$order->number}")->assertOk()->assertInertia(fn (Assert $page) => $page
        ->where('order.customer.name', 'Titular anonimizado')
        ->where('order.customer.email', null)
        ->where('order.customer.cpf', null)
        ->etc()
    );
    $this->actingAs($store)->post("/painel/pedidos/{$order->number}/cpf")->assertOk()->assertJson(['cpf' => null]);
});

it('shows an anonymized account in the panel and refuses to change it', function () {
    $admin = Member::factory()->role('admin')->create();
    $member = noticedMember(['nickname' => 'coruja']);
    $sighting = Sighting::factory()->approved()->create(['member_id' => $member->id, 'public_nickname' => 'coruja']);
    $this->travelTo(Carbon::parse('2031-03-01 03:15'));
    $this->artisan('members:anonymize-inactive')->assertSuccessful();
    $this->actingAs($admin);

    $this->get('/painel/membros')->assertInertia(fn (Assert $page) => $page
        ->where('items', fn ($items) => collect($items)->contains(fn ($row) => $row['nickname'] === 'coruja'
            && $row['anonymizedAt'] === '2031-03-01' && $row['name'] === null && $row['email'] === null))
        ->etc()
    );
    $this->get("/painel/membros/{$member->id}")->assertOk()->assertInertia(fn (Assert $page) => $page
        ->where('member.anonymizedAt', '2031-03-01')
        ->where('member.name', null)
        ->where('member.email', null)
        ->etc()
    );
    $this->put("/painel/membros/{$member->id}/papel", ['role' => 'moderator'])->assertSessionHasErrors('member');
    $this->delete("/painel/membros/{$member->id}")->assertSessionHasErrors('member');
    expect($member->fresh()->role->value)->toBe('member');

    $this->get("/painel/relatos/{$sighting->id}")->assertOk()->assertInertia(fn (Assert $page) => $page
        ->where('sighting.author.anonymized', true)
        ->where('sighting.author.email', null)
        ->etc()
    );
    $this->get('/painel/auditoria')->assertOk();
});

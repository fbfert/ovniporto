<?php

use App\Application\Members\UseCases\BuildMemberExport;
use App\Domain\Members\Contracts\IdentityProvider;
use App\Domain\Members\Data\Identity;
use App\Infrastructure\Identity\GoogleIdentityProvider;
use App\Jobs\ExportMemberData;
use App\Mail\AccountDeletedMail;
use App\Mail\MemberDataExportMail;
use App\Models\Member;
use App\Models\Sighting;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

/** Pretends Google answered the callback with this identity. */
function fakeGoogle(Identity $identity): void
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

it('asks Google only for openid, email and profile', function () {
    config(['services.google' => ['client_id' => 'id', 'client_secret' => 'secret', 'redirect' => 'http://localhost/auth/google/callback']]);

    $url = (string) $this->get('/auth/google')->assertRedirect()->headers->get('Location');
    parse_str((string) parse_url($url, PHP_URL_QUERY), $query);

    expect($url)->toStartWith('https://accounts.google.com/')
        ->and(explode(' ', $query['scope']))->toEqualCanonicalizing(GoogleIdentityProvider::SCOPES)
        ->and(GoogleIdentityProvider::SCOPES)->toEqualCanonicalizing(['openid', 'email', 'profile']);
});

it('creates the member on the first Google sign-in and sends them to the welcome screen', function () {
    fakeGoogle(new Identity('g-1', 'Ana Souza', 'ana@example.test', 'https://lh3.test/a.jpg'));

    $this->get('/auth/google/callback')->assertRedirect('/boas-vindas');

    $member = Member::query()->sole();
    expect($member->only(['google_id', 'name', 'email', 'avatar_url', 'nickname']))->toBe([
        'google_id' => 'g-1', 'name' => 'Ana Souza', 'email' => 'ana@example.test', 'avatar_url' => 'https://lh3.test/a.jpg', 'nickname' => null,
    ])->and($member->role->value)->toBe('member');
    $this->assertAuthenticatedAs($member);
});

it('signs an existing member in without creating a duplicate', function () {
    $member = Member::factory()->create(['google_id' => 'g-2']);
    fakeGoogle(new Identity('g-2', $member->name, $member->email, null));

    $this->get('/auth/google/callback')->assertRedirect('/conta');

    expect(Member::query()->count())->toBe(1);
});

it('sends members without a complete profile to the welcome screen', function () {
    $this->actingAs(Member::factory()->incomplete()->create())->get('/conta')->assertRedirect('/boas-vindas');
});

it('sends guests to the sign-in page', function () {
    $this->get('/conta')->assertRedirect('/entrar');
    $this->get('/entrar')->assertOk()->assertInertia(fn (Assert $page) => $page->component('Members/SignIn'));
});

it('suggests a nickname and refuses one already taken', function () {
    Member::factory()->create(['nickname' => 'coruja']);
    $member = Member::factory()->incomplete()->create(['name' => 'Ana Clara']);

    $this->actingAs($member)->get('/boas-vindas')
        ->assertInertia(fn (Assert $page) => $page->component('Members/Welcome')->where('suggestion', 'ana'));
    $this->getJson('/apelido-disponivel?apelido=Coruja')->assertJson(['available' => false]);
    $this->post('/boas-vindas', ['nickname' => 'coruja', 'terms' => '1'])->assertSessionHasErrors('nickname');

    expect($member->fresh()->nickname)->toBeNull();
});

it('does not activate the account without the terms', function () {
    $member = Member::factory()->incomplete()->create();

    $this->actingAs($member)->post('/boas-vindas', ['nickname' => 'vigia_nova'])->assertSessionHasErrors('terms');

    expect($member->fresh()->hasCompleteProfile())->toBeFalse();
});

it('activates the account with a free nickname and accepted terms', function () {
    $member = Member::factory()->incomplete()->create();

    $this->actingAs($member)->post('/boas-vindas', ['nickname' => 'Vigia_Nova', 'city' => 'Lages', 'terms' => '1'])
        ->assertRedirect('/conta');

    expect($member->fresh()->only(['nickname', 'city']))->toBe(['nickname' => 'vigia_nova', 'city' => 'Lages'])
        ->and($member->fresh()->terms_accepted_at)->not->toBeNull();
});

it('shows the account with only the member\'s own reports', function () {
    $member = Member::factory()->create();
    $mine = Sighting::factory()->create(['member_id' => $member->id]);
    Sighting::factory()->create(['member_id' => Member::factory()->create()->id]);

    $this->actingAs($member)->get('/conta?aba=dados')->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('Members/Account')
        ->where('tab', 'dados')
        ->has('sightings', 1)
        ->where('sightings.0.id', $mine->id)
        ->where('profile.email', $member->email)
    );
});

it('lets a member edit nickname and city, but not take someone else\'s nickname', function () {
    Member::factory()->create(['nickname' => 'farol']);
    $member = Member::factory()->create();

    $this->actingAs($member)->put('/conta/dados', ['nickname' => 'farol'])->assertSessionHasErrors('nickname');
    $this->put('/conta/dados', ['nickname' => 'neblina', 'city' => 'Painel'])->assertSessionHasNoErrors();

    expect($member->fresh()->only(['nickname', 'city']))->toBe(['nickname' => 'neblina', 'city' => 'Painel']);
});

it('forbids deleting another member\'s report', function () {
    $other = Sighting::factory()->create(['member_id' => Member::factory()->create()->id]);

    $this->actingAs(Member::factory()->create())->delete("/conta/relatos/{$other->id}")->assertForbidden();

    expect($other->fresh())->not->toBeNull();
});

it('queues the data export and e-mails the JSON', function () {
    Queue::fake();
    $member = Member::factory()->create();

    $this->actingAs($member)->post('/conta/exportar')->assertSessionHas('toast');
    Queue::assertPushed(ExportMemberData::class, fn ($job) => $job->memberId === $member->id);

    Mail::fake();
    Sighting::factory()->create(['member_id' => $member->id]);
    (new ExportMemberData($member->id))->handle(app(BuildMemberExport::class));

    Mail::assertSent(MemberDataExportMail::class, function (MemberDataExportMail $mail) use ($member) {
        $data = json_decode($mail->json, true);

        return $mail->hasTo($member->email)
            && $data['perfil']['apelido'] === $member->nickname
            && count($data['relatos']) === 1
            && array_key_exists('avise_me', $data);
    });
});

it('keeps the account when the confirmation does not match the nickname', function () {
    $member = Member::factory()->create(['nickname' => 'coruja']);

    $this->actingAs($member)->delete('/conta', ['confirmation' => 'outra'])->assertSessionHasErrors('confirmation');

    expect($member->fresh())->not->toBeNull();
});

it('deletes the account, its reports and photo files, then signs out', function () {
    Mail::fake();
    Storage::fake('public');
    Storage::fake('local');
    $member = Member::factory()->create(['nickname' => 'coruja']);
    $sighting = Sighting::factory()->create(['member_id' => $member->id]);
    Storage::disk('public')->put('sightings/a.jpg', 'x');
    $sighting->photos()->create(['path' => 'sightings/a.jpg', 'width' => 10, 'height' => 10, 'sort_order' => 0]);

    $this->actingAs($member)->delete('/conta', ['confirmation' => 'Coruja'])->assertRedirect('/');

    expect(Member::query()->find($member->id))->toBeNull()
        ->and(Sighting::query()->find($sighting->id))->toBeNull();
    Storage::disk('public')->assertMissing('sightings/a.jpg');
    Mail::assertQueued(AccountDeletedMail::class, fn ($mail) => $mail->hasTo($member->email));
    $this->assertGuest();
});

it('never shows a member\'s real name or e-mail on public pages', function () {
    $member = Member::factory()->create(['name' => 'Fulano de Tal', 'email' => 'fulano@example.test', 'nickname' => 'coruja']);
    Sighting::factory()->approved()->create(['member_id' => $member->id, 'public_nickname' => 'coruja']);

    $this->get('/')->assertSee('coruja')->assertDontSee('Fulano de Tal')->assertDontSee('fulano@example.test');
});

it('never offers the local sign-in shortcut outside the local environment', function () {
    $member = Member::factory()->create();

    app()->detectEnvironment(fn () => 'production');
    $this->get("/dev/entrar-como/{$member->id}")->assertNotFound();
    $this->assertGuest();

    app()->detectEnvironment(fn () => 'local');
    $this->get("/dev/entrar-como/{$member->id}")->assertRedirect('/conta');
    $this->assertAuthenticatedAs($member);
});

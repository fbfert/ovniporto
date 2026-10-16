<?php

use App\Domain\Privacy\ConsentType;
use App\Domain\Privacy\Contracts\ConsentLedger;
use App\Models\Consent;
use App\Models\Member;
use App\Models\RegionPartner;
use App\Models\Sighting;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Mail::fake();
    Storage::fake('local');
    Storage::fake('public');
    $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.7'])->withHeader('User-Agent', 'VigiaBrowser/1.0');
});

function lastConsent(): Consent
{
    return Consent::query()->latest('id')->firstOrFail();
}

it('records the terms and the age declaration with version, date, IP and browser when the profile is completed', function () {
    $member = Member::factory()->incomplete()->create();

    $this->actingAs($member)->post('/boas-vindas', ['nickname' => 'vigia_nova', 'terms' => '1', 'age' => '1'])->assertRedirect();

    $consents = Consent::query()->where('member_id', $member->id)->orderBy('id')->get();
    expect($consents->map->only(['type', 'version', 'ip_address', 'user_agent'])->all())->toBe([
        ['type' => 'termos', 'version' => 'inicial', 'ip_address' => '203.0.113.7', 'user_agent' => 'VigiaBrowser/1.0'],
        ['type' => 'idade_minima', 'version' => '2026-10-16', 'ip_address' => '203.0.113.7', 'user_agent' => 'VigiaBrowser/1.0'],
    ])->and($consents[0]->given_at->isToday())->toBeTrue();
});

it('records the publication consent of each report, again on resubmission', function () {
    $member = Member::factory()->create(['nickname' => 'coruja']);
    $payload = [
        'type' => 'light', 'description' => 'Uma luz verde parada sobre a serra, depois sumiu de uma vez.',
        'observedDate' => now()->toDateString(), 'timeRange' => 'night', 'lat' => -27.85, 'lng' => -50.22,
        'gaze' => 'NE', 'nickname' => 'coruja', 'consent' => '1', 'photos' => [],
    ];

    $this->actingAs($member)->post('/relatar', $payload)->assertRedirect('/relatar/enviado');
    $sighting = Sighting::query()->sole();

    expect(lastConsent()->only(['type', 'version', 'member_id', 'subject', 'ip_address']))->toBe([
        'type' => 'publicacao_relato', 'version' => '2026-10-02', 'member_id' => $member->id,
        'subject' => "relato:{$sighting->id}", 'ip_address' => '203.0.113.7',
    ]);

    $sighting->update(['status' => 'changes_requested', 'moderation_note' => 'Conte o horário.']);
    $this->put("/relatar/{$sighting->id}", [...$payload, 'keptPhotos' => []])->assertSessionHasNoErrors();

    expect(Consent::query()->where('type', 'publicacao_relato')->where('subject', "relato:{$sighting->id}")->count())->toBe(2);
});

it('records the newsletter consent with the e-mail, once', function () {
    $this->from('/')->post('/avise-me', ['email' => 'Vigia@Serra.com', 'consent' => '1']);
    $this->from('/')->post('/avise-me', ['email' => 'vigia@serra.com', 'consent' => '1']);

    expect(Consent::query()->where('type', 'newsletter')->count())->toBe(1)
        ->and(lastConsent()->only(['version', 'email', 'member_id', 'ip_address']))->toBe([
            'version' => '2026-10-01', 'email' => 'vigia@serra.com', 'member_id' => null, 'ip_address' => '203.0.113.7',
        ]);
});

it('records the partner listing consent on the date the partner gave it', function () {
    $admin = Member::factory()->role('admin')->create();
    $this->actingAs($admin)->post('/painel/regiao', ['name' => 'Pousada da Serra', 'type' => 'inn', 'city' => 'Lages']);
    $partner = RegionPartner::query()->sole();
    expect(Consent::query()->where('type', 'listagem_parceiro')->count())->toBe(0);

    $this->post("/painel/regiao/{$partner->id}", [
        'name' => 'Pousada da Serra', 'type' => 'inn', 'city' => 'Lages', 'consentGivenAt' => '2026-09-28',
    ])->assertSessionHasNoErrors();
    $this->post("/painel/regiao/{$partner->id}", [
        'name' => 'Pousada da Serra Alta', 'type' => 'inn', 'city' => 'Lages', 'consentGivenAt' => '2026-09-28',
    ])->assertSessionHasNoErrors();

    $consents = Consent::query()->where('type', 'listagem_parceiro')->get();
    expect($consents)->toHaveCount(1)
        ->and($consents[0]->subject)->toBe("parceiro:{$partner->id}")
        ->and($consents[0]->version)->toBe('2026-10-02')
        ->and($consents[0]->given_at->toDateString())->toBe('2026-09-28');
});

it('withdraws a consent with a date and keeps the record', function () {
    $this->post('/avise-me', ['email' => 'vigia@serra.com', 'consent' => '1']);
    $consent = Consent::query()->where('type', 'newsletter')->sole();

    $this->travelTo(now()->addDay());
    app(ConsentLedger::class)->withdraw(ConsentType::Newsletter, $consent->subject);
    $withdrawnAt = $consent->fresh()->withdrawn_at;

    $this->travelTo(now()->addDay());
    app(ConsentLedger::class)->withdraw(ConsentType::Newsletter, $consent->subject);

    expect($withdrawnAt)->not->toBeNull()
        ->and($consent->fresh()->withdrawn_at->equalTo($withdrawnAt))->toBeTrue()
        ->and(Consent::query()->where('type', 'newsletter')->count())->toBe(1);
});

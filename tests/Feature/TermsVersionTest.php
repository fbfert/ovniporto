<?php

use App\Models\Consent;
use App\Models\ContentBlock;
use App\Models\Member;
use Inertia\Testing\AssertableInertia as Assert;

function publishTerms(string $date): void
{
    foreach (['terms_final' => '1', 'terms_updated_at' => $date] as $key => $value) {
        ContentBlock::query()->updateOrCreate(['key' => $key], ['value' => $value]);
    }
}

it('lets members in while the terms are still a draft', function () {
    ContentBlock::query()->updateOrCreate(['key' => 'terms_updated_at'], ['value' => '2026-10-04']);

    $this->actingAs(Member::factory()->create())->get('/conta')->assertOk();
});

it('asks for the new version of the terms before going on, then records it', function () {
    $member = Member::factory()->create();
    publishTerms('2026-11-15');

    $this->actingAs($member)->get('/relatar')->assertRedirect('/termos/aceitar');
    $this->get('/termos/aceitar')->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('Members/AcceptTerms')
        ->where('version', '2026-11-15')
        ->where('pending', ['terms'])
    );

    $this->post('/termos/aceitar', [])->assertSessionHasErrors('terms');
    $this->post('/termos/aceitar', ['terms' => '1'])->assertRedirect('/relatar');

    $latest = Consent::query()->where('member_id', $member->id)->where('type', 'termos')->latest('id')->first();
    expect($latest?->version)->toBe('2026-11-15')
        ->and($member->fresh()->terms_accepted_at->isToday())->toBeTrue();
    $this->get('/relatar')->assertOk();
    $this->get('/termos/aceitar')->assertRedirect('/conta');
});

it('also guards the panel and every action of a member with outdated terms', function () {
    $moderator = Member::factory()->role('moderator')->create();
    publishTerms('2026-11-15');

    $this->actingAs($moderator)->get('/painel')->assertRedirect('/termos/aceitar');
    $this->post('/conta/exportar')->assertRedirect('/termos/aceitar');
});

it('takes the newest date between the terms and the privacy policy', function () {
    $member = Member::factory()->create();
    publishTerms('2026-11-15');
    foreach (['privacy_final' => '1', 'privacy_updated_at' => '2026-12-01'] as $key => $value) {
        ContentBlock::query()->updateOrCreate(['key' => $key], ['value' => $value]);
    }

    $this->actingAs($member)->post('/termos/aceitar', ['terms' => '1']);

    expect(Consent::query()->where('member_id', $member->id)->latest('id')->value('version'))->toBe('2026-12-01');
});

it('asks a member from before the age declaration to confirm it, then returns to the page they opened', function () {
    $member = Member::factory()->withoutAgeDeclaration()->create();

    $this->actingAs($member)->get('/relatar')->assertRedirect('/termos/aceitar');
    $this->get('/termos/aceitar')->assertInertia(fn (Assert $page) => $page->where('pending', ['age']));

    $this->post('/termos/aceitar', ['terms' => '1'])->assertSessionHasErrors('age');
    $this->post('/termos/aceitar', ['age' => '1'])->assertRedirect('/relatar');

    $age = Consent::query()->where('member_id', $member->id)->where('type', 'idade_minima')->sole();
    expect($age->version)->toBe('2026-10-16')
        ->and(Consent::query()->where('member_id', $member->id)->where('type', 'termos')->count())->toBe(1);
    $this->get('/relatar')->assertOk();
});

it('asks for the new terms and the age on one screen and records both', function () {
    $member = Member::factory()->withoutAgeDeclaration()->create();
    publishTerms('2026-11-15');

    $this->actingAs($member)->get('/conta')->assertRedirect('/termos/aceitar');
    $this->get('/termos/aceitar')->assertInertia(fn (Assert $page) => $page->where('pending', ['terms', 'age']));

    $this->post('/termos/aceitar', ['terms' => '1', 'age' => '1'])->assertRedirect('/conta');

    expect(Consent::query()->where('member_id', $member->id)->latest('id')->limit(2)->pluck('type')->all())
        ->toEqualCanonicalizing(['termos', 'idade_minima']);
});

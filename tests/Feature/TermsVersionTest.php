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

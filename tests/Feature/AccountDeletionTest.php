<?php

use App\Models\Consent;
use App\Models\Member;
use App\Models\NewsletterSubscriber;
use App\Models\Order;
use App\Models\Sighting;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\Support\Orders;

beforeEach(function () {
    Mail::fake();
    Storage::fake('local');
    Storage::fake('public');
    $this->member = Member::factory()->create(['nickname' => 'coruja', 'email' => 'coruja@serra.test']);
});

function deleteAccount(Member $member): void
{
    test()->actingAs($member)->delete('/conta', ['confirmation' => $member->nickname])->assertRedirect('/');
}

it('erases the reports with every photo variant and any leftover upload from the disk', function () {
    $sighting = Sighting::factory()->approved()->create(['member_id' => $this->member->id]);
    $photo = $sighting->photos()->create(['path' => "sightings/{$sighting->id}/1", 'width' => 1600, 'height' => 1200, 'sort_order' => 0]);
    $photo->update(['variants' => [400, 800, 1600], 'processed_at' => now()]);
    foreach ([400, 800, 1600] as $width) {
        Storage::disk('local')->put("sightings/{$sighting->id}/1-{$width}.webp", 'webp');
    }
    Storage::disk('local')->put("uploads/{$this->member->id}/esquecida.jpg", 'jpg');
    Storage::disk('local')->put('uploads/999/de-outra-pessoa.jpg', 'jpg');

    deleteAccount($this->member);

    expect(Member::query()->find($this->member->id))->toBeNull()
        ->and(Sighting::query()->count())->toBe(0)
        ->and(Storage::disk('local')->allFiles())->toBe(['uploads/999/de-outra-pessoa.jpg']);
});

it('also erases every variant when the author deletes a single report', function () {
    $sighting = Sighting::factory()->create(['member_id' => $this->member->id]);
    $photo = $sighting->photos()->create(['path' => "sightings/{$sighting->id}/1", 'width' => 800, 'height' => 600, 'sort_order' => 0]);
    $photo->update(['variants' => [400, 800], 'processed_at' => now()]);
    Storage::disk('local')->put("sightings/{$sighting->id}/1-400.webp", 'webp');
    Storage::disk('local')->put("sightings/{$sighting->id}/1-800.webp", 'webp');

    $this->actingAs($this->member)->delete("/conta/relatos/{$sighting->id}")->assertRedirect();

    expect(Storage::disk('local')->allFiles())->toBe([]);
});

it('keeps a paid order for tax law as "Titular excluído", five years after payment', function () {
    $paidAt = Carbon::parse('2026-09-20 15:00:00');
    $order = Orders::paidBy($this->member, 'coruja@serra.test', $paidAt);
    $guest = Orders::paidBy(null, 'coruja@serra.test', $paidAt);

    deleteAccount($this->member);

    foreach ([$order, $guest] as $kept) {
        $kept = $kept->fresh();
        expect($kept->number)->not->toBeEmpty()
            ->and($kept->customer_name)->toBe('Titular excluído')
            ->and($kept->customer_email)->toBeNull()
            ->and($kept->customer_phone)->toBeNull()
            ->and($kept->member_id)->toBeNull()
            ->and($kept->customer_cpf)->toBe('529.982.247-25')
            ->and($kept->total_cents)->toBe(2390)
            ->and($kept->items()->count())->toBe(1)
            ->and($kept->address)->toBe(['city' => 'Lages', 'state' => 'SC'])
            ->and($kept->retention_until->toDateTimeString())->toBe('2031-09-20 15:00:00');
    }
    expect(Order::query()->getConnection()->table('orders')->where('id', $order->id)->value('customer_cpf'))
        ->not->toContain('529.982.247-25');
});

it('keeps the consents anonymized and drops the newsletter sign-up of the account e-mail', function () {
    $this->post('/avise-me', ['email' => 'coruja@serra.test', 'consent' => '1']);
    Consent::query()->update(['ip_address' => '203.0.113.7', 'user_agent' => 'VigiaBrowser/1.0']);

    deleteAccount($this->member);

    expect(NewsletterSubscriber::query()->where('email', 'coruja@serra.test')->exists())->toBeFalse()
        ->and(Consent::query()->count())->toBe(2);
    Consent::query()->get()->each(fn (Consent $c) => expect($c->only(['member_id', 'email', 'subject', 'ip_address', 'user_agent']))
        ->toBe(['member_id' => null, 'email' => null, 'subject' => null, 'ip_address' => null, 'user_agent' => null])
        ->and($c->version)->not->toBeEmpty());
});

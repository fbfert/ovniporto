<?php

use App\Application\Members\UseCases\BuildMemberExport;
use App\Mail\MemberDataExportMail;
use App\Models\Member;
use App\Models\Sighting;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\Support\Orders;

beforeEach(function () {
    Mail::fake();
    Storage::fake('local');
});

function exportOf(Member $member): array
{
    return app(BuildMemberExport::class)->execute($member->id);
}

it('exports profile, reports, orders, sign-ups and consents of the member and of no one else', function () {
    $member = Member::factory()->create(['nickname' => 'coruja', 'email' => 'coruja@serra.test']);
    $other = Member::factory()->create(['email' => 'outra@serra.test']);

    $this->actingAs($member)->post('/relatar', [
        'type' => 'light', 'description' => 'Uma luz verde parada sobre a serra, depois sumiu de uma vez.',
        'observedDate' => now()->toDateString(), 'timeRange' => 'night', 'lat' => -27.85, 'lng' => -50.22,
        'gaze' => 'NE', 'nickname' => 'coruja', 'consent' => '1', 'photos' => [],
    ]);
    Sighting::factory()->create(['member_id' => $other->id]);
    Orders::paidBy($member, 'coruja@serra.test', now());
    Orders::paidBy(null, 'coruja@serra.test');
    Orders::paidBy($other, 'outra@serra.test', now());
    $this->post('/avise-me', ['email' => 'coruja@serra.test', 'consent' => '1']);
    $this->post('/avise-me', ['email' => 'outra@serra.test', 'consent' => '1']);

    $data = exportOf($member);

    expect($data)->toHaveKeys(['perfil', 'relatos', 'pedidos', 'avise_me', 'consentimentos'])
        ->and($data['perfil']['apelido'])->toBe('coruja')
        ->and($data['relatos'])->toHaveCount(1)
        ->and($data['pedidos'])->toHaveCount(2)
        ->and(array_column($data['pedidos'], 'email'))->each->toBe('coruja@serra.test')
        ->and($data['pedidos'][0]['itens'][0]['produto'])->toBe('Adesivo OVNIPORTO')
        ->and($data['pedidos'][0]['cpf'])->toBe('***.***.***-25')
        ->and(array_column($data['avise_me'], 'email'))->toBe(['coruja@serra.test'])
        ->and(array_column($data['consentimentos'], 'tipo'))->toEqualCanonicalizing(['termos', 'publicacao_relato', 'newsletter'])
        ->and(json_encode($data))->not->toContain('outra@serra.test')->not->toContain($other->name);
});

it('links approved photos publicly and pending ones through the report page', function () {
    $member = Member::factory()->create();
    $approved = Sighting::factory()->approved()->create(['member_id' => $member->id]);
    $pending = Sighting::factory()->create(['member_id' => $member->id]);
    foreach ([$approved, $pending] as $sighting) {
        $photo = $sighting->photos()->create(['path' => "sightings/{$sighting->id}/1", 'width' => 1600, 'height' => 1200, 'sort_order' => 0]);
        $photo->update(['variants' => [400, 800, 1600], 'processed_at' => now()]);
    }

    $reports = collect(exportOf($member)['relatos'])->keyBy('status');

    expect($reports['approved']['fotos'][0])->toMatch('#/fotos/relatos/\d+/1600$#')->not->toContain('signature')
        ->and($reports['pending']['fotos'])->toBe([route('sightings.show', ['sighting' => $pending->id])]);
});

it('still e-mails the JSON as an attachment', function () {
    $member = Member::factory()->create();

    $this->actingAs($member)->post('/conta/exportar')->assertSessionHas('toast');

    Mail::assertSent(MemberDataExportMail::class, fn ($mail) => $mail->hasTo($member->email)
        && array_key_exists('consentimentos', json_decode($mail->json, true)));
});

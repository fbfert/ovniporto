<?php

use App\Domain\Sightings\InvalidModeration;
use App\Domain\Sightings\ModerationRules;
use App\Domain\Sightings\RejectionReason;
use App\Domain\Sightings\SightingStatus;

it('approves only pending reports, publishing them', function () {
    $decision = ModerationRules::approve(SightingStatus::Pending);

    expect($decision->status)->toBe(SightingStatus::Approved)->and($decision->publish)->toBeTrue();
    expect(fn () => ModerationRules::approve(SightingStatus::Rejected))->toThrow(InvalidModeration::class);
    expect(fn () => ModerationRules::approve(SightingStatus::ChangesRequested))->toThrow(InvalidModeration::class);
});

it('needs a real message to request changes, even on a published report', function () {
    $decision = ModerationRules::requestChanges(SightingStatus::Approved, '  Tire a placa do carro.  ');

    expect($decision->status)->toBe(SightingStatus::ChangesRequested)
        ->and($decision->noteToAuthor)->toBe('Tire a placa do carro.')
        ->and($decision->publish)->toBeFalse();
    expect(fn () => ModerationRules::requestChanges(SightingStatus::Pending, 'curto'))->toThrow(InvalidModeration::class);
});

it('rejects with the reason label, or the written one for "other"', function () {
    expect(ModerationRules::reject(SightingStatus::Pending, RejectionReason::Offensive, null)->noteToAuthor)->toBe('Conteúdo ofensivo')
        ->and(ModerationRules::reject(SightingStatus::Pending, RejectionReason::Other, 'Relato duplicado do mesmo evento.')->noteToAuthor)
        ->toBe('Relato duplicado do mesmo evento.');
    expect(fn () => ModerationRules::reject(SightingStatus::Pending, RejectionReason::Other, ''))->toThrow(InvalidModeration::class);
    expect(fn () => ModerationRules::reject(SightingStatus::Approved, RejectionReason::Offensive, null))->toThrow(InvalidModeration::class);
});

it('unpublishes approved reports back to pending, with a note', function () {
    expect(ModerationRules::unpublish(SightingStatus::Approved, 'Ponto parece residência.')->status)->toBe(SightingStatus::Pending);
    expect(fn () => ModerationRules::unpublish(SightingStatus::Approved, ''))->toThrow(InvalidModeration::class);
    expect(fn () => ModerationRules::unpublish(SightingStatus::Pending, 'Ponto parece residência.'))->toThrow(InvalidModeration::class);
});

it('lets the author resend only what the tower sent back', function () {
    expect(ModerationRules::canResubmit(SightingStatus::ChangesRequested))->toBeTrue()
        ->and(ModerationRules::canResubmit(SightingStatus::Pending))->toBeFalse()
        ->and(ModerationRules::canResubmit(SightingStatus::Rejected))->toBeFalse();
});

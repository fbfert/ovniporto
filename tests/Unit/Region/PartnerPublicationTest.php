<?php

use App\Domain\Region\PartnerPublication;

$now = new DateTimeImmutable('2026-10-01 12:00');

it('never publishes a partner without recorded consent', function () use ($now) {
    expect(PartnerPublication::isPublic(null, $now->modify('-1 day'), $now))->toBeFalse();
});

it('publishes a partner with consent and a past publication date', function () use ($now) {
    expect(PartnerPublication::isPublic($now->modify('-3 days'), $now->modify('-1 day'), $now))->toBeTrue();
});

it('waits for a scheduled publication date', function () use ($now) {
    expect(PartnerPublication::isPublic($now->modify('-3 days'), $now->modify('+1 day'), $now))->toBeFalse();
});

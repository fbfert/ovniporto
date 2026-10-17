<?php

use App\Domain\Orders\InvalidReturnTransition;
use App\Domain\Orders\ReturnRequestStatus as Status;
use App\Domain\Orders\WithdrawalWindow;

it('lets a return go forward and close, from received or in progress', function (Status $from, Status $to) {
    $from->assertCanMoveTo($to);

    expect(true)->toBeTrue();
})->with([
    [Status::Received, Status::InProgress],
    [Status::Received, Status::Completed],
    [Status::Received, Status::Declined],
    [Status::InProgress, Status::Completed],
    [Status::InProgress, Status::Declined],
]);

it('refuses to move a return back or reopen it', function (Status $from, Status $to) {
    expect(fn () => $from->assertCanMoveTo($to))->toThrow(InvalidReturnTransition::class);
})->with([
    [Status::Completed, Status::InProgress],
    [Status::Declined, Status::Received],
    [Status::InProgress, Status::Received],
    [Status::Received, Status::Received],
]);

it('counts 7 calendar days from the delivery', function (string $requested, bool $within) {
    $delivered = new DateTimeImmutable('2026-10-01 14:00');

    expect(WithdrawalWindow::covers($delivered, new DateTimeImmutable($requested)))->toBe($within);
})->with([
    '5 days later' => ['2026-10-06 10:00', true],
    'exactly 7 days' => ['2026-10-08 14:00', true],
    '20 days later' => ['2026-10-21 10:00', false],
]);

it('keeps an order not delivered yet within the window', function () {
    expect(WithdrawalWindow::covers(null, new DateTimeImmutable('2030-01-01')))->toBeTrue();
});

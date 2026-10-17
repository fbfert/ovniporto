<?php

use App\Application\Orders\UseCases\RequestReturn;
use App\Domain\Orders\OrderStatus;
use App\Domain\Orders\ReturnReason;
use App\Domain\Orders\ReturnRefused;
use Tests\Support\MemoryReturns;

beforeEach(function () {
    $order = fn (int $id, ?string $email, OrderStatus $status, ?string $deliveredAt = null) => [
        'id' => $id, 'email' => $email, 'status' => $status, 'deliveredAt' => $deliveredAt === null ? null : new DateTimeImmutable($deliveredAt),
    ];
    $this->returns = new MemoryReturns([
        'OVP-2026-000001' => $order(1, 'ana@example.com', OrderStatus::Delivered, '2026-10-11 10:00'),
        'OVP-2026-000002' => $order(2, 'ana@example.com', OrderStatus::Delivered, '2026-09-26 10:00'),
        'OVP-2026-000003' => $order(3, 'ana@example.com', OrderStatus::Shipped),
        'OVP-2026-000004' => $order(4, 'ana@example.com', OrderStatus::PendingPayment),
        'OVP-2026-000005' => $order(5, 'ana@example.com', OrderStatus::Canceled),
        'OVP-2026-000006' => $order(6, null, OrderStatus::Delivered),
    ]);
    $this->request = fn (string $number, string $email = 'ana@example.com', ReturnReason $reason = ReturnReason::Withdrawal) => (new RequestReturn($this->returns, $this->returns))
        ->execute($number, $email, ' Ana Serra ', $reason, ' Não serviu na parede. ', new DateTimeImmutable('2026-10-16 10:00'));
});

it('accepts a paid order whose number and e-mail match, and tells both sides', function () {
    $result = ($this->request)(' ovp-2026-000001 ', ' Ana@Example.com ');

    expect($result)->toBe(['protocol' => 'DEV-2026-000001', 'withinWithdrawal' => true])
        ->and($this->returns->created[0])->toMatchArray(['orderId' => 1, 'name' => 'Ana Serra', 'email' => 'ana@example.com', 'message' => 'Não serviu na parede.'])
        ->and($this->returns->sent)->toBe([['protocol' => 'DEV-2026-000001', 'email' => 'ana@example.com']]);
});

it('accepts and marks a request out of the withdrawal window', function () {
    expect(($this->request)('OVP-2026-000002', reason: ReturnReason::Defect)['withinWithdrawal'])->toBeFalse()
        ->and($this->returns->created[0]['deliveredAt'])->toEqual(new DateTimeImmutable('2026-09-26 10:00'));
});

it('keeps an order not delivered yet within the window', function () {
    expect(($this->request)('OVP-2026-000003')['withinWithdrawal'])->toBeTrue();
});

it('refuses with the same words whatever did not match', function (string $number, string $email) {
    expect(fn () => ($this->request)($number, $email))->toThrow(ReturnRefused::class, 'Não encontramos um pedido com esse número e esse e-mail.')
        ->and($this->returns->created)->toBe([])
        ->and($this->returns->sent)->toBe([]);
})->with([
    'unknown number' => ['OVP-2026-999999', 'ana@example.com'],
    'other e-mail' => ['OVP-2026-000001', 'bia@example.com'],
    'unpaid order' => ['OVP-2026-000004', 'ana@example.com'],
    'canceled order' => ['OVP-2026-000005', 'ana@example.com'],
    'anonymized order' => ['OVP-2026-000006', 'ana@example.com'],
]);

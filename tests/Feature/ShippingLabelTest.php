<?php

use App\Application\Orders\UseCases\ChangeOrderStatus;
use App\Domain\Orders\InvalidOrderTransition;
use App\Domain\Orders\OrderStatus;
use App\Domain\Shipping\Contracts\ShippingProvider;
use App\Mail\OrderMail;
use App\Models\Order;
use Illuminate\Support\Facades\Mail;
use Tests\Support\FakeShipping;

beforeEach(function () {
    Mail::fake();
    $this->shipping = new FakeShipping;
    app()->instance(ShippingProvider::class, $this->shipping);
});

function orderIn(OrderStatus $status, array $overrides = []): Order
{
    static $n = 0;
    $n++;

    return Order::query()->create([
        'number' => sprintf('OVP-2026-%06d', $n),
        'status' => $status,
        'customer_name' => 'Ana Serra',
        'customer_email' => 'ana@example.com',
        'customer_phone' => '49999990000',
        'customer_cpf' => '52998224725',
        'address' => ['cep' => '88501-000', 'street' => 'Rua A', 'number' => '1', 'complement' => null, 'district' => 'Centro', 'city' => 'Lages', 'state' => 'SC'],
        'pickup' => false,
        'shipping_option_id' => '1',
        'subtotal_cents' => 1600,
        'shipping_cents' => 1250,
        'total_cents' => 2850,
        ...$overrides,
    ]);
}

it('refuses a label for an unpaid order without calling the provider', function () {
    $order = orderIn(OrderStatus::PendingPayment);

    expect(fn () => app(ChangeOrderStatus::class)->createLabel($order->number))->toThrow(InvalidArgumentException::class);
    expect($this->shipping->labels)->toBe(0);
});

it('buys the label for a paid order and keeps the tracking code', function () {
    $order = orderIn(OrderStatus::Paid);

    app(ChangeOrderStatus::class)->createLabel($order->number);

    expect($order->fresh())->shipment_id->toBe('ME-1')->tracking_code->toBe('AA123456789BR')
        ->and($this->shipping->labels)->toBe(1);
});

it('refuses skipping a step and records nothing', function () {
    $order = orderIn(OrderStatus::PendingPayment);

    expect(fn () => app(ChangeOrderStatus::class)->move($order->number, OrderStatus::Shipped, 'operator'))
        ->toThrow(InvalidOrderTransition::class);
    expect($order->fresh()->status)->toBe(OrderStatus::PendingPayment)
        ->and($order->events()->count())->toBe(0);
});

it('marks shipped orders delivered when the provider says so, by the system', function () {
    $order = orderIn(OrderStatus::Shipped, ['shipment_id' => 'ME-1']);
    $this->shipping->delivered = true;

    $this->artisan('orders:track-shipments')->assertSuccessful();

    expect($order->fresh()->status)->toBe(OrderStatus::Delivered)
        ->and($order->events()->sole())->actor->toBe('system');
    Mail::assertQueued(OrderMail::class, fn ($mail) => str_contains($mail->subjectLine, 'Entregue'));
});

it('e-mails the buyer when production starts', function () {
    $order = orderIn(OrderStatus::Paid);

    app(ChangeOrderStatus::class)->move($order->number, OrderStatus::InProduction, 'operator');

    Mail::assertQueued(OrderMail::class, fn ($mail) => $mail->hasTo('ana@example.com') && str_contains($mail->subjectLine, 'Em produção'));
});

<?php

namespace App\Application\Orders\UseCases;

use App\Domain\Orders\Contracts\OrderNotifier;
use App\Domain\Orders\Contracts\OrderRepository;
use App\Domain\Orders\OrderStatus;
use App\Domain\Shipping\Contracts\ShippingProvider;
use App\Domain\Shipping\Data\Shipment;
use DateTimeImmutable;
use InvalidArgumentException;

/**
 * Status moves after payment (panel, scheduled jobs). The state machine
 * refuses skipped steps before anything is written; every move is an event
 * with its author, and the buyer hears about the ones that matter.
 */
final readonly class ChangeOrderStatus
{
    public const ABANDON_AFTER_HOURS = 2;

    public function __construct(
        private OrderRepository $orders,
        private OrderNotifier $notifier,
        private ShippingProvider $shipping,
    ) {}

    public function move(string $orderNumber, OrderStatus $to, string $actor, ?int $actorId = null, ?string $note = null): void
    {
        $order = $this->orders->findByNumber($orderNumber) ?? throw new InvalidArgumentException('Pedido não encontrado.');
        $this->orders->locked($order['id'], function (array $fresh) use ($to, $actor, $actorId, $note) {
            $fresh['status']->assertCanMoveTo($to);
            $this->orders->changeStatus($fresh['id'], $fresh['status'], $to, $actor, $actorId, $note);
        });
        $this->notifier->statusChanged($order['number'], $to);
    }

    /** @return int how many pending orders older than 2 hours were canceled */
    public function cancelAbandoned(DateTimeImmutable $now): int
    {
        $canceled = 0;
        foreach ($this->orders->abandonedBefore($now->modify('-'.self::ABANDON_AFTER_HOURS.' hours')) as $orderId) {
            $canceled += $this->orders->locked($orderId, function (array $order) {
                if ($order['status'] !== OrderStatus::PendingPayment) {
                    return 0;
                }
                $this->orders->changeStatus($order['id'], OrderStatus::PendingPayment, OrderStatus::Canceled, 'system', null, 'Pagamento não concluído em 2 horas.');

                return 1;
            });
        }

        return $canceled;
    }

    /** Buys the label: only for a paid order (or in production) going by mail; the provider is not called otherwise. */
    public function createLabel(string $orderNumber): void
    {
        $order = $this->orders->findByNumber($orderNumber) ?? throw new InvalidArgumentException('Pedido não encontrado.');
        if (! $order['status']->allowsShippingLabel()) {
            throw new InvalidArgumentException('Etiqueta só para pedido pago ou em produção.');
        }
        $data = $this->orders->shipmentData($order['id']);
        if ($order['pickup'] || $data === null) {
            throw new InvalidArgumentException('Pedido de retirada em Lages não tem etiqueta.');
        }

        $label = $this->shipping->createLabel(new Shipment(
            orderNumber: $order['number'],
            serviceId: $data['serviceId'],
            recipientName: $data['name'],
            recipientEmail: $data['email'],
            recipientPhone: $data['phone'],
            recipientCpf: $data['cpf'],
            address: $data['address'],
            items: $data['items'],
        ));
        $this->orders->setShipment($order['id'], $label['shipmentId'], $label['trackingCode'], $label['trackingUrl']);
    }

    /** @return int how many shipped orders the provider reports delivered */
    public function markDelivered(): int
    {
        $delivered = 0;
        foreach ($this->orders->inTransit() as $shipment) {
            if (! $this->shipping->isDelivered($shipment['shipmentId'])) {
                continue;
            }
            $number = $this->orders->locked($shipment['id'], function (array $order) {
                if ($order['status'] !== OrderStatus::Shipped) {
                    return null;
                }
                $this->orders->changeStatus($order['id'], OrderStatus::Shipped, OrderStatus::Delivered, 'system', null, null);

                return $order['number'];
            });
            if ($number !== null) {
                $this->notifier->statusChanged($number, OrderStatus::Delivered);
                $delivered++;
            }
        }

        return $delivered;
    }
}

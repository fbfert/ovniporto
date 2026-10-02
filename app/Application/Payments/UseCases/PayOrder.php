<?php

namespace App\Application\Payments\UseCases;

use App\Application\Orders\UseCases\ManageCart;
use App\Domain\Orders\Contracts\OrderNotifier;
use App\Domain\Orders\Contracts\OrderRepository;
use App\Domain\Orders\Data\CartOwner;
use App\Domain\Orders\OrderStatus;
use App\Domain\Payments\Contracts\PaymentGateway;
use App\Domain\Payments\Data\CaptureResult;
use App\Domain\Payments\Data\WebhookNotice;
use App\Domain\Payments\PaymentUnavailable;
use DateTimeImmutable;
use InvalidArgumentException;

/**
 * From "pay" to "paid". The browser's return and the provider's webhook both
 * end in markPaid(), which holds a lock on the order: whichever arrives
 * second finds it paid and does nothing — one paid event, stock taken once.
 */
final readonly class PayOrder
{
    public function __construct(
        private OrderRepository $orders,
        private PaymentGateway $gateway,
        private ManageCart $cart,
        private OrderNotifier $notifier,
    ) {}

    /** Creates (once) the provider order with the server's own total. */
    public function start(string $orderNumber): string
    {
        $order = $this->pending($orderNumber);
        if ($order['paymentOrderId'] !== null) {
            return $order['paymentOrderId'];
        }
        $id = $this->gateway->createOrder($order['number'], $order['totalCents'], "Pedido {$order['number']} · OVNIPORTO");
        $this->orders->setPaymentOrderId($order['id'], $id);

        return $id;
    }

    /** The buyer approved in the provider's window: capture on the server, then mark paid. */
    public function approve(string $orderNumber): OrderStatus
    {
        $order = $this->orders->findByNumber($orderNumber) ?? throw new InvalidArgumentException('Pedido não encontrado.');
        if ($order['status'] !== OrderStatus::PendingPayment || $order['paymentOrderId'] === null) {
            return $order['status'];
        }

        $capture = $this->gateway->capture($order['paymentOrderId']);
        if (! $capture->isCompleted()) {
            if ($capture->status === CaptureResult::DECLINED) {
                throw new PaymentUnavailable('O pagamento não foi aprovado. Tente outro cartão ou o Pix.');
            }

            return OrderStatus::PendingPayment; // under review: the webhook will finish it
        }

        return $this->markPaid($order['id'], $capture->captureId, $capture->amountCents);
    }

    /** A verified webhook: the capture already happened at the provider. */
    public function confirmFromWebhook(WebhookNotice $notice): void
    {
        if (! $notice->isCaptureCompleted() || $notice->providerOrderId === null) {
            return;
        }
        $order = $this->orders->findByPaymentOrderId($notice->providerOrderId);
        if ($order !== null) {
            $this->markPaid($order['id'], $notice->captureId, $notice->amountCents);
        }
    }

    private function markPaid(int $orderId, ?string $captureId, int $amountCents): OrderStatus
    {
        $result = $this->orders->locked($orderId, function (array $order) use ($captureId, $amountCents) {
            if ($order['status'] === OrderStatus::Canceled) {
                // Paid after the 2-hour cancellation (a late Pix): never silently kept, a human refunds it.
                $this->orders->addNote($order['id'], OrderStatus::Canceled, 'provider', "Pagamento {$captureId} chegou após o cancelamento: reembolsar.");

                return ['status' => OrderStatus::Canceled, 'alert' => true, 'paid' => false, 'order' => $order];
            }
            if ($order['status'] !== OrderStatus::PendingPayment) {
                return ['status' => $order['status'], 'alert' => false, 'paid' => false, 'order' => $order];
            }
            if ($amountCents !== $order['totalCents']) {
                $this->orders->addNote($order['id'], OrderStatus::PendingPayment, 'provider', "Valor capturado ({$amountCents}) diferente do total: conferir.");

                return ['status' => OrderStatus::PendingPayment, 'alert' => true, 'paid' => false, 'order' => $order];
            }

            $this->orders->changeStatus($order['id'], OrderStatus::PendingPayment, OrderStatus::Paid, 'provider', null, null, [
                'paid_at' => new DateTimeImmutable,
                'payment_capture_id' => $captureId,
            ]);
            $this->orders->decrementStock($order['id']);

            return ['status' => OrderStatus::Paid, 'alert' => false, 'paid' => true, 'order' => $order];
        });

        $order = $result['order'];
        if ($result['paid']) {
            $this->emptyCart($order['memberId'], $order['cartToken']);
            $this->notifier->statusChanged($order['number'], OrderStatus::Paid);
        }
        if ($result['alert']) {
            $this->notifier->alertStore($order['number'], 'Pagamento precisa de conferência manual.');
        }

        return $result['status'];
    }

    private function emptyCart(?int $memberId, ?string $cartToken): void
    {
        $owner = $memberId !== null ? CartOwner::member($memberId) : ($cartToken !== null ? CartOwner::visitor($cartToken) : null);
        if ($owner === null) {
            return;
        }
        foreach ($this->cart->view($owner)['items'] as $item) {
            $this->cart->remove($owner, (int) $item['variantId']);
        }
    }

    /** @return array{id: int, number: string, status: OrderStatus, totalCents: int, paymentOrderId: ?string, memberId: ?int, cartToken: ?string, pickup: bool, shipmentId: ?string} */
    private function pending(string $orderNumber): array
    {
        $order = $this->orders->findByNumber($orderNumber) ?? throw new InvalidArgumentException('Pedido não encontrado.');
        if ($order['status'] !== OrderStatus::PendingPayment) {
            throw new InvalidArgumentException('Esse pedido não está esperando pagamento.');
        }

        return $order;
    }
}

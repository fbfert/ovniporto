<?php

namespace App\Application\Orders\UseCases;

use App\Domain\Audit\Contracts\Auditor;
use App\Domain\Audit\Data\AuditEntry;
use App\Domain\Orders\Contracts\OrderAdminRepository;
use App\Domain\Orders\Contracts\OrderRepository;
use App\Domain\Orders\OrderStatus;
use App\Domain\Payments\Contracts\PaymentGateway;
use DateTimeImmutable;
use InvalidArgumentException;

/**
 * /painel/pedidos (store and admin). Only the actions the current status
 * allows are offered, and the state machine refuses the rest anyway. Every
 * action is audited and becomes an order event with its author.
 */
final readonly class OperateOrders
{
    public const PER_PAGE = 25;

    public function __construct(
        private OrderAdminRepository $admin,
        private OrderRepository $orders,
        private ChangeOrderStatus $status,
        private PaymentGateway $gateway,
        private Auditor $auditor,
    ) {}

    /** @return array{items: list<array<string, mixed>>, total: int, counts: array<string, int>, page: int, hasMore: bool} */
    public function list(?OrderStatus $status, ?string $query, int $page, DateTimeImmutable $now): array
    {
        $page = max(1, $page);
        $result = $this->admin->search($status?->value, $query, $page, self::PER_PAGE);
        $result['items'] = array_map(fn (array $item) => [
            ...$item,
            'ageHours' => intdiv(max(0, $now->getTimestamp() - (new DateTimeImmutable((string) $item['createdAt']))->getTimestamp()), 3600),
        ], $result['items']);

        return [...$result, 'page' => $page, 'hasMore' => $page * self::PER_PAGE < $result['total']];
    }

    /** @return array{order: array<string, mixed>, actions: list<string>, supplierSummary: string}|null */
    public function sheet(string $number): ?array
    {
        $order = $this->admin->sheet($number);
        if ($order === null) {
            return null;
        }

        return [
            'order' => $order,
            'actions' => self::actionsFor(OrderStatus::from($order['status']), (bool) $order['pickup'], $order['tracking'] !== null),
            'supplierSummary' => self::supplierSummary($order),
        ];
    }

    /**
     * What the sheet offers for each status (the server checks again when it runs).
     *
     * @return list<string>
     */
    public static function actionsFor(OrderStatus $status, bool $pickup, bool $hasTracking): array
    {
        return match ($status) {
            OrderStatus::PendingPayment => ['cancel'],
            OrderStatus::Paid => ['production', 'refund'],
            OrderStatus::InProduction => array_values(array_filter([$pickup || $hasTracking ? null : 'label', 'ship', 'refund'])),
            OrderStatus::Shipped => ['deliver', 'refund'],
            OrderStatus::Delivered => ['refund'],
            OrderStatus::Canceled, OrderStatus::Refunded => [],
        };
    }

    /** The CPF in full, once, with an audit record of who saw it. */
    public function revealCpf(int $actorId, string $number): ?string
    {
        return $this->auditor->audited(
            new AuditEntry($actorId, 'order.cpf_revealed', 'order', $this->id($number), ['number' => $number]),
            fn () => null,
            fn () => $this->admin->cpfOf($number),
        );
    }

    public function startProduction(int $actorId, string $number): void
    {
        $this->move($actorId, $number, OrderStatus::InProduction, 'order.production_started');
    }

    public function createLabel(int $actorId, string $number): void
    {
        $this->record($actorId, $number, 'order.label_created', [], fn () => $this->status->createLabel($number));
    }

    /** A tracking code typed by hand (bought outside the panel) is kept before the e-mail goes out. */
    public function markShipped(int $actorId, string $number, ?string $trackingCode): void
    {
        $trackingCode = $trackingCode === null ? null : strtoupper(trim($trackingCode));
        if ($trackingCode !== null && $trackingCode !== '') {
            $this->admin->setTracking($number, $trackingCode, null);
        }
        $this->move($actorId, $number, OrderStatus::Shipped, 'order.shipped', ['tracking' => $trackingCode]);
    }

    public function markDelivered(int $actorId, string $number): void
    {
        $this->move($actorId, $number, OrderStatus::Delivered, 'order.delivered');
    }

    /** Before payment only: after it, canceling means refunding. */
    public function cancel(int $actorId, string $number, string $reason): void
    {
        $this->move($actorId, $number, OrderStatus::Canceled, 'order.canceled', ['reason' => self::reason($reason)], self::reason($reason));
    }

    /** Gives the money back through the provider; in-stock items return to the shelf if they never left. */
    public function refund(int $actorId, string $number, string $reason): void
    {
        $reason = self::reason($reason);
        $order = $this->orders->findByNumber($number) ?? throw new InvalidArgumentException('Pedido não encontrado.');
        $order['status']->assertCanMoveTo(OrderStatus::Refunded);
        $captureId = $this->admin->captureIdOf($number) ?? throw new InvalidArgumentException('Esse pedido não tem pagamento capturado.');

        $this->gateway->refund($captureId, $order['totalCents'], $number);
        $backOnShelf = in_array($order['status'], [OrderStatus::Paid, OrderStatus::InProduction], true);
        $this->record($actorId, $number, 'order.refunded', ['reason' => $reason], function () use ($actorId, $number, $reason, $order, $backOnShelf) {
            $this->status->move($number, OrderStatus::Refunded, 'operator', $actorId, "Reembolso: {$reason}");
            if ($backOnShelf) {
                $this->orders->restoreStock($order['id'], $actorId);
            }
        });
    }

    /** @return list<array<string, string>> */
    public function export(DateTimeImmutable $from, DateTimeImmutable $to): array
    {
        return $this->admin->exportRows($from->setTime(0, 0), $to->setTime(23, 59, 59));
    }

    /** @param array<string, mixed> $order */
    private static function supplierSummary(array $order): string
    {
        /** @var list<array{quantity: int, name: string, variant: string, sku: string}> $items */
        $items = $order['items'];
        $lines = array_map(fn (array $i) => "- {$i['quantity']}x {$i['name']} · {$i['variant']} (SKU {$i['sku']})", $items);

        return "Pedido {$order['number']}\n".implode("\n", $lines);
    }

    /** @param array<string, mixed> $context */
    private function move(int $actorId, string $number, OrderStatus $to, string $action, array $context = [], ?string $note = null): void
    {
        $this->record($actorId, $number, $action, $context, fn () => $this->status->move($number, $to, 'operator', $actorId, $note));
    }

    /** @param array<string, mixed> $context */
    private function record(int $actorId, string $number, string $action, array $context, callable $change): void
    {
        $this->auditor->audited(
            new AuditEntry($actorId, $action, 'order', $this->id($number), ['number' => $number, ...$context]),
            fn () => ['status' => $this->orders->findByNumber($number)['status']->value ?? null],
            $change,
        );
    }

    private function id(string $number): int
    {
        return ($this->orders->findByNumber($number) ?? throw new InvalidArgumentException('Pedido não encontrado.'))['id'];
    }

    private static function reason(string $reason): string
    {
        $reason = trim($reason);
        if (mb_strlen($reason) < 5) {
            throw new InvalidArgumentException('Registre o motivo.');
        }

        return $reason;
    }
}

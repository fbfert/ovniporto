<?php

namespace App\Application\Orders\UseCases;

use App\Domain\Orders\Contracts\ReturnNotifier;
use App\Domain\Orders\Contracts\ReturnRequestRepository;
use App\Domain\Orders\OrderStatus;
use App\Domain\Orders\ReturnReason;
use App\Domain\Orders\ReturnRefused;
use App\Domain\Orders\WithdrawalWindow;
use DateTimeImmutable;

/**
 * "/devolucao": a buyer asks to send an order back. Accepted only when the number and the e-mail
 * belong to the same paid order; anything else gets the same refusal. Out-of-window requests are
 * accepted and only marked: the team decides, the buyer always gets an answer.
 */
final readonly class RequestReturn
{
    /** Orders that were never paid have nothing to send back. */
    private const NOT_RETURNABLE = [OrderStatus::PendingPayment, OrderStatus::Canceled];

    public function __construct(
        private ReturnRequestRepository $returns,
        private ReturnNotifier $notifier,
    ) {}

    /** @return array{protocol: string, withinWithdrawal: bool} */
    public function execute(string $orderNumber, string $email, string $name, ReturnReason $reason, string $message, DateTimeImmutable $now): array
    {
        $email = mb_strtolower(trim($email));
        $order = $this->returns->orderForReturn(mb_strtoupper(trim($orderNumber)));
        // An anonymized order has no e-mail left to match.
        if ($order === null || $order['email'] !== $email || in_array($order['status'], self::NOT_RETURNABLE, true)) {
            throw new ReturnRefused;
        }

        $within = WithdrawalWindow::covers($order['deliveredAt'], $now);
        $protocol = $this->returns->create($order['id'], trim($name), $email, $reason, trim($message), $within, $order['deliveredAt']);
        $this->notifier->received($protocol, $email);

        return ['protocol' => $protocol, 'withinWithdrawal' => $within];
    }
}

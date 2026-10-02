<?php

namespace App\Infrastructure\Mail;

use App\Domain\Catalog\Money;
use App\Domain\Members\MemberRole;
use App\Domain\Orders\Contracts\OrderNotifier;
use App\Domain\Orders\OrderStatus;
use App\Mail\OrderMail;
use App\Models\Member;
use App\Models\Order;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

/** Queued e-mails to the buyer, with the signed link to the order page. */
final class MailOrderNotifier implements OrderNotifier
{
    public function received(string $orderNumber): void
    {
        $this->send($orderNumber, fn (Order $o) => new OrderMail(
            "Pedido {$o->number} recebido",
            'Pedido recebido',
            [
                "O pedido {$o->number} chegou, no total de R$ ".$this->reais($o->total_cents).'.',
                'Ele fica reservado por 2 horas esperando o pagamento. Se precisar, o botão abaixo leva de volta a ele.',
            ],
            $this->link($o),
        ));
    }

    public function statusChanged(string $orderNumber, OrderStatus $status): void
    {
        $this->send($orderNumber, fn (Order $o) => match ($status) {
            OrderStatus::Paid => new OrderMail("Pagamento confirmado · {$o->number}", 'Pagamento confirmado', [
                'Recebemos o pagamento do pedido '.$o->number.'.',
                $o->pickup ? 'Avisamos por aqui e pelo WhatsApp quando estiver pronto para retirar em Lages.' : 'Agora ele entra na fila de produção e envio.',
            ], $this->link($o)),
            OrderStatus::InProduction => new OrderMail("Em produção · {$o->number}", 'Em produção', [
                'Seu pedido está sendo feito à mão em Lages. Assim que sair, mandamos o rastreio.',
            ], $this->link($o)),
            OrderStatus::Shipped => new OrderMail("Enviado · {$o->number}", 'A caminho', array_filter([
                'Seu pedido saiu de Lages.',
                $o->tracking_code ? "Código de rastreio: {$o->tracking_code}" : null,
            ]), $this->link($o), 'Acompanhar o pedido'),
            OrderStatus::Delivered => new OrderMail("Entregue · {$o->number}", 'Pousou', [
                'O pedido '.$o->number.' foi entregue. Cola o adesivo, manda foto pra gente.',
            ], $this->link($o)),
            default => null,
        });
    }

    public function alertStore(string $orderNumber, string $message): void
    {
        Member::query()
            ->whereIn('role', [MemberRole::Store->value, MemberRole::Admin->value])
            ->pluck('email')
            ->each(fn (string $email) => Mail::to($email)->queue(new OrderMail(
                "Atenção no pedido {$orderNumber}",
                'Pedido precisa de você',
                [$message, 'Os detalhes ficam no histórico do pedido, no painel.'],
                url('/painel'),
                'Abrir o painel',
            )));
    }

    /** @param callable(Order): ?OrderMail $build */
    private function send(string $orderNumber, callable $build): void
    {
        $order = Order::query()->where('number', $orderNumber)->first();
        if ($order === null || $order->customer_email === null) {
            return;
        }
        $mail = $build($order);
        if ($mail !== null) {
            Mail::to($order->customer_email)->queue($mail);
        }
    }

    private function link(Order $order): string
    {
        return URL::signedRoute('order.show', ['number' => $order->number]);
    }

    private function reais(int $cents): string
    {
        return str_replace('.', ',', Money::cents($cents)->decimal());
    }
}

<?php

namespace App\Infrastructure\Members;

use App\Domain\Catalog\Money;
use App\Domain\Members\Contracts\MemberDataSource;
use App\Models\Order;
use App\Models\OrderItem;

/**
 * "Pedidos" of "Baixar meus dados": orders placed while signed in, and guest
 * orders with the same e-mail (the same person). The CPF goes masked: the file
 * travels by e-mail.
 */
final class OrdersDataSource implements MemberDataSource
{
    public function section(): string
    {
        return 'pedidos';
    }

    public function exportFor(int $memberId, string $email): array
    {
        return Order::query()
            ->with('items')
            ->where(fn ($q) => $q->where('member_id', $memberId)->orWhere('customer_email', mb_strtolower($email)))
            ->orderBy('id')
            ->get()
            ->map(fn (Order $o) => [
                'numero' => $o->number,
                'status' => $o->status->value,
                'feito_em' => $o->created_at->toIso8601String(),
                'pago_em' => $o->paid_at?->toIso8601String(),
                'nome' => $o->customer_name,
                'email' => $o->customer_email,
                'telefone' => $o->customer_phone,
                'cpf' => self::maskCpf($o->customer_cpf),
                'entrega' => $o->pickup ? 'retirada em Lages' : $o->address,
                'itens' => $o->items->map(fn (OrderItem $i) => [
                    'produto' => $i->product_name,
                    'variante' => $i->variant_name,
                    'quantidade' => $i->quantity,
                    'preco_unitario' => Money::cents($i->unit_price_cents)->decimal(),
                ])->values()->all(),
                'frete' => Money::cents($o->shipping_cents)->decimal(),
                'total' => Money::cents($o->total_cents)->decimal(),
                'rastreio' => $o->tracking_code,
            ])
            ->values()
            ->all();
    }

    private static function maskCpf(?string $cpf): ?string
    {
        $digits = preg_replace('/\D/', '', (string) $cpf);

        return $digits === '' || $digits === null ? null : '***.***.***-'.substr($digits, -2);
    }
}

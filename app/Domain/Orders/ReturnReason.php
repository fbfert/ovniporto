<?php

namespace App\Domain\Orders;

/** Why someone asks to send an order back. Withdrawal is the CDC art. 49 right, no reason needed. */
enum ReturnReason: string
{
    case Withdrawal = 'withdrawal';
    case Defect = 'defect';
    case WrongItem = 'wrong_item';
    case TransportDamage = 'transport_damage';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Withdrawal => 'Arrependimento',
            self::Defect => 'Defeito',
            self::WrongItem => 'Produto diferente do pedido',
            self::TransportDamage => 'Avaria no transporte',
            self::Other => 'Outro motivo',
        };
    }
}

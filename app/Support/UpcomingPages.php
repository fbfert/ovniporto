<?php

namespace App\Support;

/**
 * Public routes that exist in the menu but are built by later OpenSpec changes.
 * Each one renders an honest "em construção" page instead of a 404.
 */
final class UpcomingPages
{
    /** @var array<string, array{title: string, eyebrow: string, description: string, phase: string, change: string}> */
    public const PAGES = [
        'finalizar' => [
            'title' => 'Finalizar compra',
            'eyebrow' => 'Quase lá',
            'description' => 'O pagamento (cartão e Pix pelo PayPal) e o frete pelo Melhor Envio estão sendo ligados. Seu carrinho fica guardado até lá.',
            'phase' => 'Fase 4 · Loja',
            'change' => 'add-checkout-payments',
        ],
    ];
}

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
        'loja' => [
            'title' => 'Loja',
            'eyebrow' => 'Lembranças de',
            'description' => 'O adesivo já está pronto pra colar. Camiseta, caneca e o Kit Abdução entram depois, impressos sob pedido.',
            'phase' => 'Fase 4 · Loja',
            'change' => 'add-store-catalog',
        ],
    ];
}

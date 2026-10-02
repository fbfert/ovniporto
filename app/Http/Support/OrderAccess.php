<?php

namespace App\Http\Support;

use Illuminate\Http\Request;

/**
 * Who may see or pay an order: the member who owns it, whoever opens the
 * signed link from the e-mail, or the browser session that just placed it.
 */
final class OrderAccess
{
    private const SESSION_KEY = 'placed_orders';

    public static function remember(Request $request, string $number): void
    {
        $placed = (array) $request->session()->get(self::SESSION_KEY, []);
        $request->session()->put(self::SESSION_KEY, array_values(array_unique([...$placed, $number])));
    }

    public static function allows(Request $request, string $number, ?int $ownerId): bool
    {
        $memberId = $request->user()?->getAuthIdentifier();
        if ($ownerId !== null && $memberId !== null && (int) $memberId === $ownerId) {
            return true;
        }
        if (in_array($number, (array) $request->session()->get(self::SESSION_KEY, []), true)) {
            return true;
        }

        return $request->hasValidSignature();
    }
}

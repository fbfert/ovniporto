<?php

namespace App\Http\Support;

use App\Domain\Orders\Data\CartOwner;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Whose cart a request talks about. A visitor gets a random token in the
 * session on the first item added (reading never creates one); the session
 * keeps it across the sign-in, when it is merged into the member's cart.
 */
final class CartOwners
{
    public const SESSION_KEY = 'cart_token';

    public static function forRead(Request $request): ?CartOwner
    {
        $memberId = $request->user()?->getAuthIdentifier();
        if ($memberId !== null) {
            return CartOwner::member((int) $memberId);
        }
        $token = $request->session()->get(self::SESSION_KEY);

        return is_string($token) ? CartOwner::visitor($token) : null;
    }

    public static function forWrite(Request $request): CartOwner
    {
        $owner = self::forRead($request);
        if ($owner !== null) {
            return $owner;
        }
        $token = (string) Str::uuid();
        $request->session()->put(self::SESSION_KEY, $token);

        return CartOwner::visitor($token);
    }
}

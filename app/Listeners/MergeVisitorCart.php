<?php

namespace App\Listeners;

use App\Application\Orders\UseCases\ManageCart;
use App\Http\Support\CartOwners;
use Illuminate\Auth\Events\Login;
use Illuminate\Http\Request;

/** What a visitor put in the cart before signing in stays in the cart after. */
class MergeVisitorCart
{
    public function __construct(
        private ManageCart $carts,
        private Request $request,
    ) {}

    public function handle(Login $event): void
    {
        if (! $this->request->hasSession()) {
            return;
        }
        $token = $this->request->session()->pull(CartOwners::SESSION_KEY);
        if (is_string($token)) {
            $this->carts->merge($token, (int) $event->user->getAuthIdentifier());
        }
    }
}

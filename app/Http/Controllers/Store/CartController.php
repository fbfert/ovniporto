<?php

namespace App\Http\Controllers\Store;

use App\Application\Orders\UseCases\ManageCart;
use App\Domain\Orders\CartRefused;
use App\Http\Controllers\Controller;
use App\Http\Support\CartOwners;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * The drawer talks to these through Inertia; the updated cart comes back as
 * the shared "cart" prop. Only a variant id and a quantity are ever read:
 * any price the browser sends is ignored.
 */
class CartController extends Controller
{
    public function add(Request $request, ManageCart $cart): RedirectResponse
    {
        $data = $request->validate(['variantId' => ['required', 'integer'], 'quantity' => ['nullable', 'integer', 'min:1', 'max:999']]);

        return $this->run(fn () => $cart->add(CartOwners::forWrite($request), (int) $data['variantId'], (int) ($data['quantity'] ?? 1)))
            ->with('cartOpen', true);
    }

    public function update(Request $request, ManageCart $cart, int $variant): RedirectResponse
    {
        $data = $request->validate(['quantity' => ['required', 'integer', 'min:0', 'max:999']]);

        return $this->run(fn () => $cart->update(CartOwners::forWrite($request), $variant, (int) $data['quantity']));
    }

    public function remove(Request $request, ManageCart $cart, int $variant): RedirectResponse
    {
        $owner = CartOwners::forRead($request);
        if ($owner !== null) {
            $cart->remove($owner, $variant);
        }

        return back();
    }

    private function run(Closure $action): RedirectResponse
    {
        try {
            $action();
        } catch (CartRefused $e) {
            throw ValidationException::withMessages(['quantity' => $e->getMessage()]);
        }

        return back();
    }
}

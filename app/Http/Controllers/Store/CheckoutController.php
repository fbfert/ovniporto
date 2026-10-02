<?php

namespace App\Http\Controllers\Store;

use App\Application\Orders\UseCases\ManageCart;
use App\Application\Orders\UseCases\PlaceOrder;
use App\Domain\Members\Contracts\MemberRepository;
use App\Domain\Members\MemberBlocked;
use App\Domain\Orders\Contracts\OrderRepository;
use App\Domain\Orders\Cpf;
use App\Domain\Orders\Phone;
use App\Domain\Payments\Contracts\PaymentGateway;
use App\Domain\Shipping\Cep;
use App\Domain\Shipping\Contracts\AddressLookup;
use App\Domain\Shipping\Contracts\ShippingProvider;
use App\Domain\Shipping\Data\ShippingOption;
use App\Domain\Shipping\ShippingUnavailable;
use App\Http\Controllers\Controller;
use App\Http\Support\CartOwners;
use App\Http\Support\OrderAccess;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use InvalidArgumentException;

/**
 * /checkout in three steps: who (validated here, nothing stored yet), where
 * (CEP → address, freight quoted on the server) and review. Placing the order
 * re-validates everything; the payment happens on the order's own page.
 */
class CheckoutController extends Controller
{
    public function show(Request $request, ManageCart $cart, MemberRepository $members, ShippingProvider $shipping): Response|RedirectResponse
    {
        $owner = CartOwners::forRead($request);
        if ($owner === null || $cart->view($owner)['items'] === []) {
            return redirect()->route('store')->with('toast', 'Seu carrinho está vazio.');
        }
        $memberId = $request->user()?->getAuthIdentifier();
        $profile = $memberId === null ? null : $members->find((int) $memberId);

        return Inertia::render('Checkout/Checkout', [
            'customer' => ['name' => $profile['name'] ?? '', 'email' => $profile['email'] ?? ''],
            'signedIn' => $profile !== null,
            'shippingAvailable' => ! $shipping->isSimulated(),
        ]);
    }

    public function identify(Request $request): RedirectResponse
    {
        $this->customer($request);

        return back();
    }

    /** Address from the CEP and the freight options, both from the server. */
    public function delivery(Request $request, AddressLookup $addresses, ShippingProvider $shipping, ManageCart $cart): JsonResponse
    {
        $data = $request->validate(['cep' => ['required', 'string', 'max:12']]);
        try {
            $cep = Cep::from($data['cep']);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage(), 'errors' => ['cep' => [$e->getMessage()]]], 422);
        }

        $owner = CartOwners::forRead($request);
        $view = $owner === null ? ['weightGrams' => 0, 'subtotalCents' => 0] : $cart->view($owner);
        $options = [];
        $message = null;
        if ($shipping->isSimulated()) {
            $message = 'O envio pelos Correios ainda não está ligado. Dá para retirar em Lages.';
        } else {
            try {
                $options = array_map(fn (ShippingOption $o) => $o->toArray(), $shipping->quote($cep, $view['weightGrams'], $view['subtotalCents']));
            } catch (ShippingUnavailable $e) {
                $message = $e->getMessage();
            }
        }

        return response()->json([
            'cep' => $cep->formatted(),
            'address' => $addresses->find($cep),
            'options' => $options,
            'message' => $message,
        ]);
    }

    public function place(Request $request, PlaceOrder $place): RedirectResponse
    {
        $customer = $this->customer($request);
        $pickup = $request->boolean('pickup');
        $address = $pickup ? null : $request->validate([
            'address.cep' => ['required', 'string', 'max:12'],
            'address.street' => ['required', 'string', 'max:120'],
            'address.number' => ['required', 'string', 'max:20'],
            'address.complement' => ['nullable', 'string', 'max:60'],
            'address.district' => ['required', 'string', 'max:80'],
            'address.city' => ['required', 'string', 'max:80'],
            'address.state' => ['required', 'string', 'size:2'],
            'shippingOptionId' => ['required', 'string', 'max:60'],
        ], [
            'address.number.required' => 'Diga o número.',
            'shippingOptionId.required' => 'Escolha como receber.',
        ])['address'];

        $order = $this->refusals(fn () => $place->execute(
            CartOwners::forWrite($request),
            $customer,
            $address === null ? null : [...$address, 'complement' => $address['complement'] ?? null],
            $pickup ? null : $request->string('shippingOptionId')->toString(),
        ));
        OrderAccess::remember($request, $order['number']);

        return redirect()->to(URL::signedRoute('order.pay', ['number' => $order['number']]));
    }

    public function pay(Request $request, OrderRepository $orders, PaymentGateway $gateway, string $number): Response|RedirectResponse
    {
        $page = $orders->page($number) ?? abort(404);
        abort_unless(OrderAccess::allows($request, $number, $page['memberId']), 403);
        // The e-mail link may open in another browser: from here on, this session may pay.
        OrderAccess::remember($request, $number);
        if ($page['status'] !== 'pending_payment') {
            return redirect()->to(URL::signedRoute('order.show', ['number' => $number]));
        }

        return Inertia::render('Checkout/Pay', [
            'order' => $page,
            'payment' => [
                'simulated' => $gateway->isSimulated(),
                'clientId' => config('services.paypal.client_id'),
            ],
        ]);
    }

    /** @return array{name: string, email: string, phone: string, cpf: string} */
    private function customer(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:160'],
            'phone' => ['required', 'string', 'max:20'],
            'cpf' => ['required', 'string', 'max:14'],
        ], [
            'name.required' => 'Diga seu nome.',
            'email.required' => 'Diga seu e-mail.',
            'phone.required' => 'O telefone (WhatsApp) é obrigatório.',
            'cpf.required' => 'O CPF é obrigatório para o envio.',
        ]);
        $errors = [];
        if (! Cpf::isValid((string) preg_replace('/\D/', '', $data['cpf']))) {
            $errors['cpf'] = 'CPF inválido. Confira os números.';
        }
        try {
            Phone::from($data['phone']);
        } catch (InvalidArgumentException $e) {
            $errors['phone'] = $e->getMessage();
        }
        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        return $data;
    }

    /**
     * @template T
     *
     * @param  Closure(): T  $action
     * @return T
     */
    private function refusals(Closure $action): mixed
    {
        try {
            return $action();
        } catch (ShippingUnavailable $e) {
            throw ValidationException::withMessages(['shippingOptionId' => $e->getMessage()]);
        } catch (MemberBlocked|InvalidArgumentException $e) {
            throw ValidationException::withMessages(['checkout' => $e->getMessage()]);
        }
    }
}

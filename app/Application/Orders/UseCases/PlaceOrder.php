<?php

namespace App\Application\Orders\UseCases;

use App\Domain\Catalog\Money;
use App\Domain\Members\Contracts\MemberRepository;
use App\Domain\Members\MemberBlocked;
use App\Domain\Orders\Contracts\OrderNotifier;
use App\Domain\Orders\Contracts\OrderRepository;
use App\Domain\Orders\Cpf;
use App\Domain\Orders\Data\CartOwner;
use App\Domain\Orders\Data\NewOrder;
use App\Domain\Orders\OrderTotals;
use App\Domain\Orders\Phone;
use App\Domain\Shipping\Cep;
use App\Domain\Shipping\Contracts\ShippingProvider;
use App\Domain\Shipping\Data\ShippingOption;
use App\Domain\Shipping\ShippingUnavailable;
use InvalidArgumentException;

/**
 * "Revisar e pagar": turns the cart into an order. Nothing the browser says
 * about money is used: items are priced from the catalog and the freight is
 * quoted again here. Stock is not touched until the payment is confirmed.
 */
final readonly class PlaceOrder
{
    public function __construct(
        private ManageCart $cart,
        private ShippingProvider $shipping,
        private OrderRepository $orders,
        private OrderNotifier $notifier,
        private MemberRepository $members,
    ) {}

    /**
     * @param  array{name: string, email: string, phone: string, cpf: string}  $customer
     * @param  array{cep: string, street: string, number: string, complement: ?string, district: string, city: string, state: string}|null  $address  null = pickup in Lages
     * @return array{id: int, number: string}
     */
    public function execute(CartOwner $owner, array $customer, ?array $address, ?string $shippingOptionId): array
    {
        if ($owner->memberId !== null && $this->members->isBlocked($owner->memberId)) {
            throw new MemberBlocked;
        }
        $cart = $this->cart->view($owner);
        if ($cart['items'] === []) {
            throw new InvalidArgumentException('Seu carrinho está vazio.');
        }

        $cpf = Cpf::from($customer['cpf']);
        $phone = Phone::from($customer['phone']);
        $shipping = $address === null ? null : $this->chosenOption($address, $shippingOptionId, $cart['weightGrams'], $cart['subtotalCents']);

        $lines = array_map(fn (array $item) => ['unitPrice' => Money::cents($item['unitPriceCents']), 'quantity' => $item['quantity']], $cart['items']);
        $totals = OrderTotals::of($lines, Money::cents($shipping === null ? 0 : $shipping->priceCents));

        $order = $this->orders->create(new NewOrder(
            memberId: $owner->memberId,
            cartToken: $owner->token,
            name: trim($customer['name']),
            email: mb_strtolower(trim($customer['email'])),
            phone: $phone->digits,
            cpf: $cpf->digits,
            address: $address === null ? null : [...$address, 'cep' => Cep::from($address['cep'])->formatted()],
            shipping: $shipping,
            items: array_map(fn (array $item) => [
                'variantId' => (int) $item['variantId'],
                'productName' => (string) $item['productName'],
                'variantName' => (string) $item['variantName'],
                'unitPriceCents' => (int) $item['unitPriceCents'],
                'quantity' => (int) $item['quantity'],
                'madeToOrder' => (bool) $item['madeToOrder'],
                'productionDays' => (int) $item['productionDays'],
                'weightGrams' => (int) $item['weightGrams'],
            ], $cart['items']),
            totals: $totals,
        ));
        $this->notifier->received($order['number']);

        return $order;
    }

    /**
     * Quotes again on the server and picks the option by id: a price sent by the browser is never used.
     *
     * @param  array{cep: string}  $address
     */
    private function chosenOption(array $address, ?string $optionId, int $weightGrams, int $valueCents): ShippingOption
    {
        if ($this->shipping->isSimulated()) {
            throw new ShippingUnavailable('O envio pelos Correios ainda não está ligado. Escolha retirar em Lages.');
        }
        foreach ($this->shipping->quote(Cep::from($address['cep']), $weightGrams, $valueCents) as $option) {
            if ($option->id === $optionId) {
                return $option;
            }
        }

        throw new InvalidArgumentException('Escolha uma das opções de frete.');
    }
}

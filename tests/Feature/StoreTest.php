<?php

use App\Domain\Members\Contracts\IdentityProvider;
use App\Domain\Members\Data\Identity;
use App\Domain\Shipping\Cep;
use App\Domain\Shipping\Contracts\ShippingProvider;
use App\Domain\Shipping\Data\Shipment;
use App\Domain\Shipping\Data\ShippingOption;
use App\Models\CampaignSetting;
use App\Models\Member;
use App\Models\Product;
use App\Models\ProductVariant;
use Database\Seeders\ProductSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->seed(ProductSeeder::class);
    $this->sticker = ProductVariant::query()->where('sku', 'OVP-ADESIVO')->sole();
});

function spyShipping(): object
{
    $spy = new class implements ShippingProvider
    {
        public int $calls = 0;

        public function quote(Cep $to, int $weightGrams, int $valueCents): array
        {
            $this->calls++;

            return [new ShippingOption('pac', 'Correios', 'PAC', 2190, 8)];
        }

        public function createLabel(Shipment $shipment): array
        {
            return ['shipmentId' => 'x', 'trackingCode' => null, 'trackingUrl' => null];
        }

        public function isDelivered(string $shipmentId): bool
        {
            return false;
        }

        public function isSimulated(): bool
        {
            return false;
        }
    };
    app()->instance(ShippingProvider::class, $spy);

    return $spy;
}

it('opens with only the sticker active', function () {
    expect(Product::query()->where('is_active', true)->pluck('slug')->all())->toBe(['adesivo-ovniporto']);

    $this->get('/loja')->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('Store/Index')
        ->has('products', 1)
        ->where('products.0.slug', 'adesivo-ovniporto')
        ->where('products.0.madeToOrder', false)
    );
    $this->get('/loja/camiseta-ovniporto')->assertNotFound();
});

it('shows the runway block only when the store share is set', function () {
    $this->get('/loja')->assertInertia(fn (Assert $page) => $page->where('storeSharePercent', null));

    CampaignSetting::query()->create(['status' => 'planning', 'store_share_percent' => 10]);
    $this->get('/loja')->assertInertia(fn (Assert $page) => $page->where('storeSharePercent', 10));
});

it('marks a made-to-order product with its production days', function () {
    Product::query()->where('slug', 'camiseta-ovniporto')->update(['is_active' => true]);

    $this->get('/loja')->assertInertia(fn (Assert $page) => $page
        ->where('products.1.madeToOrder', true)
        ->where('products.1.productionDays', 10)
    );
});

it('opens the product page with variants and Product structured data', function () {
    $this->get('/loja/adesivo-ovniporto')->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('Store/Product')
        ->where('product.name', 'Adesivo OVNIPORTO')
        ->where('product.variants.0.max', 500)
        ->where('seo.jsonLd.0.@type', 'Product')
        ->where('seo.jsonLd.0.offers.price', '8.00')
        ->where('seo.jsonLd.0.offers.priceCurrency', 'BRL')
        ->where('seo.jsonLd.0.offers.availability', 'https://schema.org/InStock')
    );
});

it('quotes shipping for a valid CEP', function () {
    $spy = spyShipping();

    $this->postJson('/loja/frete', ['cep' => '88501-000', 'variantId' => $this->sticker->id, 'quantity' => 2])
        ->assertOk()
        ->assertJsonPath('cep', '88501-000')
        ->assertJsonPath('options.0.service', 'PAC')
        ->assertJsonPath('options.0.priceCents', 2190);
    expect($spy->calls)->toBe(1);
});

it('refuses an invalid CEP without asking the provider', function () {
    $spy = spyShipping();

    $this->postJson('/loja/frete', ['cep' => '88501', 'variantId' => $this->sticker->id])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('cep');
    expect($spy->calls)->toBe(0);
});

it('prices the cart on the server, ignoring a price sent by the browser', function () {
    $this->post('/carrinho/itens', ['variantId' => $this->sticker->id, 'quantity' => 3, 'priceCents' => 1])
        ->assertSessionHasNoErrors()
        ->assertSessionHas('cartOpen', true);

    $this->get('/loja')->assertInertia(fn (Assert $page) => $page
        ->where('cart.count', 3)
        ->where('cart.subtotalCents', 2400)
        ->where('cart.items.0.unitPriceCents', 800)
    );
});

it('changes and removes cart items', function () {
    $this->post('/carrinho/itens', ['variantId' => $this->sticker->id]);
    $this->patch("/carrinho/itens/{$this->sticker->id}", ['quantity' => 5])->assertSessionHasNoErrors();
    $this->get('/loja')->assertInertia(fn (Assert $page) => $page->where('cart.count', 5)->where('cart.subtotalCents', 4000));

    $this->delete("/carrinho/itens/{$this->sticker->id}");
    $this->get('/loja')->assertInertia(fn (Assert $page) => $page->where('cart.count', 0)->has('cart.items', 0));
});

it('refuses more than the stock and tells the maximum', function () {
    $this->post('/carrinho/itens', ['variantId' => $this->sticker->id, 'quantity' => 600])
        ->assertSessionHasErrors(['quantity' => 'Temos só 500 disponíveis. Ajuste a quantidade.']);
});

it('refuses an inactive product or a sold-out variant', function () {
    $shirt = Product::query()->where('slug', 'camiseta-ovniporto')->sole()
        ->variants()->create(['name' => 'M', 'sku' => 'OVP-CAM-M']);
    $this->post('/carrinho/itens', ['variantId' => $shirt->id])->assertSessionHasErrors('quantity');

    $this->sticker->update(['stock_qty' => 0]);
    $this->post('/carrinho/itens', ['variantId' => $this->sticker->id])
        ->assertSessionHasErrors(['quantity' => 'Esse item não está disponível agora.']);
});

it('keeps the visitor cart when they sign in with Google', function () {
    $member = Member::factory()->create(['google_id' => 'g-cart']);
    app()->instance(IdentityProvider::class, new class($member) implements IdentityProvider
    {
        public function __construct(private Member $member) {}

        public function redirectUrl(): string
        {
            return 'https://accounts.google.test/consent';
        }

        public function identity(): Identity
        {
            return new Identity('g-cart', $this->member->name, $this->member->email, null);
        }
    });
    $this->post('/carrinho/itens', ['variantId' => $this->sticker->id, 'quantity' => 2]);

    $this->get('/auth/google/callback')->assertRedirect();
    $this->assertAuthenticatedAs($member);

    $this->get('/loja')->assertInertia(fn (Assert $page) => $page->where('cart.count', 2));
    $this->assertDatabaseHas('carts', ['member_id' => $member->id]);
    $this->assertDatabaseCount('carts', 1);
});

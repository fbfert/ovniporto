<?php

use App\Domain\Orders\OrderStatus;
use App\Domain\Payments\Contracts\PaymentGateway;
use App\Domain\Shipping\Contracts\ShippingProvider;
use App\Mail\OrderMail;
use App\Models\AuditLog;
use App\Models\Member;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use Database\Seeders\ProductSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Support\FakeGateway;
use Tests\Support\FakeShipping;
use Tests\Support\JpegWithExif;

beforeEach(function () {
    Mail::fake();
    Storage::fake('public');
    $this->seed(ProductSeeder::class);
    $this->sticker = ProductVariant::query()->where('sku', 'OVP-ADESIVO')->sole();
    $this->gateway = new FakeGateway;
    $this->shipping = new FakeShipping;
    app()->instance(PaymentGateway::class, $this->gateway);
    app()->instance(ShippingProvider::class, $this->shipping);
    $this->store = Member::factory()->role('store')->create();
});

/** An order straight in the database, in the given status, with two stickers. */
function storeOrder(OrderStatus $status, array $overrides = []): Order
{
    static $n = 100;
    $n++;
    $order = Order::query()->create([
        'number' => sprintf('OVP-2026-%06d', $n),
        'status' => $status,
        'customer_name' => 'Ana Serra',
        'customer_email' => 'ana@example.com',
        'customer_phone' => '49999990000',
        'customer_cpf' => '52998224725',
        'address' => ['cep' => '88501-000', 'street' => 'Rua A', 'number' => '1', 'complement' => null, 'district' => 'Centro', 'city' => 'Lages', 'state' => 'SC'],
        'pickup' => false,
        'shipping_option_id' => '1',
        'subtotal_cents' => 1600,
        'shipping_cents' => 1250,
        'total_cents' => 2850,
        'payment_order_id' => $status === OrderStatus::PendingPayment ? null : "PAYPAL-{$n}",
        'payment_capture_id' => $status === OrderStatus::PendingPayment ? null : "CAPTURE-{$n}",
        'paid_at' => $status === OrderStatus::PendingPayment ? null : now(),
        ...$overrides,
    ]);
    $order->items()->create([
        'product_variant_id' => ProductVariant::query()->where('sku', 'OVP-ADESIVO')->value('id'),
        'product_name' => 'Adesivo OVNIPORTO', 'variant_name' => 'Único', 'sku' => 'OVP-ADESIVO',
        'unit_price_cents' => 800, 'quantity' => 2, 'line_cents' => 1600, 'weight_grams' => 20,
    ]);

    return $order;
}

it('lists orders by status with search, for the store role only', function () {
    $paid = storeOrder(OrderStatus::Paid);
    storeOrder(OrderStatus::PendingPayment, ['customer_name' => 'Bruno Vigia']);

    $this->actingAs($this->store)->get('/painel/pedidos?status=paid')->assertInertia(fn (Assert $page) => $page
        ->component('Panel/Orders/Index')
        ->where('total', 1)
        ->where('items.0.number', $paid->number)
        ->where('items.0.items', 2)
        ->where('counts.pending_payment', 1)
    );
    $this->get('/painel/pedidos?busca=bruno')->assertInertia(fn (Assert $page) => $page->where('total', 1));
    $this->actingAs(Member::factory()->role('moderator')->create())->get('/painel/pedidos')->assertForbidden();
});

it('masks the CPF on the sheet and reveals it on request, audited', function () {
    $order = storeOrder(OrderStatus::Paid);

    $this->actingAs($this->store)->get("/painel/pedidos/{$order->number}")->assertInertia(fn (Assert $page) => $page
        ->component('Panel/Orders/Show')
        ->where('order.customer.cpf', '***.982.247-**')
        ->where('actions', ['production', 'refund'])
        ->where('supplierSummary', fn (string $s) => str_contains($s, '2x Adesivo OVNIPORTO · Único (SKU OVP-ADESIVO)'))
    );

    $this->postJson("/painel/pedidos/{$order->number}/cpf")->assertJsonPath('cpf', '52998224725');
    expect(AuditLog::query()->where('action', 'order.cpf_revealed')->sole()->actor_id)->toBe($this->store->id);
});

it('moves a paid order into production and tells the buyer', function () {
    $order = storeOrder(OrderStatus::Paid);

    $this->actingAs($this->store)->post("/painel/pedidos/{$order->number}/producao")->assertSessionHasNoErrors();

    expect($order->fresh()->status)->toBe(OrderStatus::InProduction)
        ->and($order->events()->sole())->actor->toBe('operator')->actor_id->toBe($this->store->id);
    Mail::assertQueued(OrderMail::class, fn ($mail) => str_contains($mail->subjectLine, 'Em produção'));
});

it('refuses a label for an unpaid order and never calls the provider', function () {
    $order = storeOrder(OrderStatus::PendingPayment);

    $this->actingAs($this->store)->post("/painel/pedidos/{$order->number}/etiqueta")->assertSessionHasErrors('order');

    expect($this->shipping->labels)->toBe(0)->and($order->fresh()->shipment_id)->toBeNull();
});

it('marks shipped with tracking and e-mails the code', function () {
    $order = storeOrder(OrderStatus::InProduction);

    $this->actingAs($this->store)->post("/painel/pedidos/{$order->number}/enviado", ['tracking' => 'aa123456789br'])->assertSessionHasNoErrors();

    expect($order->fresh())->status->toBe(OrderStatus::Shipped)->tracking_code->toBe('AA123456789BR');
    Mail::assertQueued(OrderMail::class, fn ($mail) => in_array('Código de rastreio: AA123456789BR', $mail->lines, true));
});

it('refuses an invalid transition', function () {
    $order = storeOrder(OrderStatus::PendingPayment);

    $this->actingAs($this->store)->post("/painel/pedidos/{$order->number}/entregue")->assertSessionHasErrors('order');
    expect($order->fresh()->status)->toBe(OrderStatus::PendingPayment);
});

it('refunds a paid order through the provider, with the author, and puts stock back', function () {
    $order = storeOrder(OrderStatus::Paid);
    $this->sticker->update(['stock_qty' => 498]);

    $this->actingAs($this->store)->post("/painel/pedidos/{$order->number}/reembolsar", ['reason' => 'Cliente desistiu'])->assertSessionHasErrors('confirm');
    $this->post("/painel/pedidos/{$order->number}/reembolsar", ['reason' => 'Cliente desistiu', 'confirm' => true])->assertSessionHasNoErrors();

    expect($order->fresh()->status)->toBe(OrderStatus::Refunded)
        ->and($this->gateway->refunds)->toBe([['captureId' => $order->payment_capture_id, 'amountCents' => 2850]])
        ->and($order->events()->where('to_status', 'refunded')->sole())->actor_id->toBe($this->store->id)
        ->and($this->sticker->fresh()->stock_qty)->toBe(500)
        ->and(DB::table('stock_movements')->where('order_id', $order->id)->value('delta'))->toBe(2);
});

it('cancels a pending order with a reason', function () {
    $order = storeOrder(OrderStatus::PendingPayment);

    $this->actingAs($this->store)->post("/painel/pedidos/{$order->number}/cancelar", ['reason' => 'Pedido duplicado'])->assertSessionHasNoErrors();

    expect($order->fresh()->status)->toBe(OrderStatus::Canceled)
        ->and($order->events()->sole()->note)->toBe('Pedido duplicado');
});

it('prints the production order as a PDF', function () {
    $order = storeOrder(OrderStatus::Paid);

    $response = $this->actingAs($this->store)->get("/painel/pedidos/{$order->number}/ordem-de-producao.pdf")->assertOk();

    expect($response->headers->get('Content-Type'))->toBe('application/pdf')
        ->and(substr((string) $response->getContent(), 0, 4))->toBe('%PDF');
});

it('exports one row per order of the period', function () {
    $inside = storeOrder(OrderStatus::Paid);
    $outside = storeOrder(OrderStatus::Paid);
    $outside->forceFill(['created_at' => now()->subMonths(2)])->save();

    $csv = $this->actingAs($this->store)
        ->get('/painel/pedidos/exportar?de='.now()->startOfMonth()->toDateString().'&ate='.now()->endOfMonth()->toDateString())
        ->assertOk()
        ->streamedContent();

    $lines = array_values(array_filter(explode("\n", trim($csv))));
    expect($lines)->toHaveCount(2)
        ->and($csv)->toContain($inside->number)->toContain('2x Adesivo OVNIPORTO (Único)')->toContain('28.50')
        ->not->toContain($outside->number);
});

it('creates a product, inactive, and edits it with a live price', function () {
    $this->actingAs($this->store)->post('/painel/produtos', [
        'name' => 'Camiseta Torre', 'price' => '79.90', 'weightGrams' => 250, 'madeToOrder' => true, 'productionDays' => 10,
    ])->assertSessionHasNoErrors();

    $product = Product::query()->where('name', 'Camiseta Torre')->sole();
    expect($product)->price_cents->toBe(7990)->is_active->toBeFalse()->kind->toBe('made_to_order');
    $this->get('/loja/'.$product->slug)->assertNotFound();
});

it('stays on the new product after "Salvar" and returns to the list after "Salvar e voltar"', function () {
    $draft = ['price' => '19.90', 'weightGrams' => 20];

    $stay = $this->actingAs($this->store)->post('/painel/produtos', [...$draft, 'name' => 'Ímã Torre']);
    $stay->assertRedirect('/painel/produtos/'.Product::query()->where('name', 'Ímã Torre')->sole()->id);

    $this->post('/painel/produtos', [...$draft, 'name' => 'Chaveiro Torre', 'returnToList' => true])
        ->assertRedirect('/painel/produtos')
        ->assertSessionHas('toast');
    expect(Product::query()->where('name', 'Chaveiro Torre')->exists())->toBeTrue();
});

it('keeps package sides down to a tenth of a millimetre and refuses finer ones', function () {
    $draft = ['name' => 'Adesivo grande', 'price' => '12.00', 'weightGrams' => 15];

    $this->actingAs($this->store)->post('/painel/produtos', [...$draft, 'length' => '21.05', 'width' => '14.8', 'height' => '0.02'])
        ->assertSessionHasNoErrors();
    expect(Product::query()->where('name', 'Adesivo grande')->sole()->dimensions)
        ->toBe(['length' => 21.05, 'width' => 14.8, 'height' => 0.02]);

    $this->post('/painel/produtos', [...$draft, 'name' => 'Adesivo fino', 'length' => '21.055', 'width' => '14.8', 'height' => '0'])
        ->assertSessionHasErrors(['length', 'height']);
});

it('refuses a product photo without alt text and crops the accepted ones square', function () {
    $product = Product::query()->where('slug', 'adesivo-ovniporto')->sole();
    $photo = fn () => UploadedFile::fake()->createWithContent('foto.jpg', JpegWithExif::make(1200, 800));

    $this->actingAs($this->store)->post("/painel/produtos/{$product->id}/imagens", ['image' => $photo(), 'alt' => ''])->assertSessionHasErrors('alt');
    $this->post("/painel/produtos/{$product->id}/imagens", ['image' => $photo(), 'alt' => 'Adesivo colado num capacete'])->assertSessionHasNoErrors();

    $path = $product->images()->sole()->path;
    [$width, $height] = getimagesizefromstring((string) Storage::disk('public')->get($path));
    expect($width)->toBe($height);
});

it('keeps the stock history of a manual count', function () {
    $product = Product::query()->where('slug', 'adesivo-ovniporto')->sole();

    $this->actingAs($this->store)->post("/painel/produtos/{$product->id}/variantes/{$this->sticker->id}/estoque", ['quantity' => 480, 'reason' => 'Contagem do estoque'])
        ->assertSessionHasNoErrors();

    $movement = DB::table('stock_movements')->sole();
    expect($this->sticker->fresh()->stock_qty)->toBe(480)
        ->and($movement->delta)->toBe(-20)
        ->and($movement->actor_id)->toBe($this->store->id)
        ->and($movement->reason)->toBe('Contagem do estoque');
});

it('refuses a duplicated SKU', function () {
    $product = Product::query()->where('slug', 'adesivo-ovniporto')->sole();

    $this->actingAs($this->store)->post("/painel/produtos/{$product->id}/variantes", ['name' => 'Outro', 'sku' => 'ovp-adesivo'])
        ->assertSessionHasErrors('sku');
});

it('keeps products away from moderators', function () {
    $this->actingAs(Member::factory()->role('moderator')->create())->get('/painel/produtos')->assertForbidden();
});

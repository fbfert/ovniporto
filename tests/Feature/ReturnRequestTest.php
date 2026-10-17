<?php

use App\Application\Members\UseCases\BuildMemberExport;
use App\Domain\Orders\Contracts\OrderRepository;
use App\Mail\ReturnReceivedMail;
use App\Mail\ReturnRequestedMail;
use App\Models\AuditLog;
use App\Models\Member;
use App\Models\Order;
use App\Models\OrderEvent;
use App\Models\ReturnRequest;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Support\Orders;

beforeEach(function () {
    Mail::fake();
    config(['ovniporto.returns_email' => 'contato@tars.art.br']);
    $this->order = Orders::paidBy(null, 'ana@example.com', now()->subDays(10));
});

function deliveredOn(Order $order, DateTimeInterface $at): void
{
    $order->update(['status' => 'delivered']);
    OrderEvent::query()->create(['order_id' => $order->id, 'from_status' => 'shipped', 'to_status' => 'delivered', 'actor' => 'operator', 'created_at' => $at]);
}

/** @param array<string, string> $overrides */
function returnForm(Order $order, array $overrides = []): array
{
    return [
        'orderNumber' => strtolower($order->number),
        'email' => ' Ana@Example.com ',
        'name' => 'Ana Serra',
        'reason' => 'withdrawal',
        'message' => 'Comprei de presente e não serviu.',
        ...$overrides,
    ];
}

it('opens the form with the order number from the order page', function () {
    $this->get('/devolucao?pedido='.$this->order->number)->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('Store/Return')
        ->where('orderNumber', $this->order->number)
        ->where('reasons.0', ['value' => 'withdrawal', 'label' => 'Arrependimento'])
        ->where('sent', null)
    );
    $this->get('/devolucao?pedido=qualquer-coisa')->assertInertia(fn (Assert $page) => $page->where('orderNumber', ''));
});

it('stores the request with a protocol, shows it and tells the buyer and the team', function () {
    deliveredOn($this->order, now()->subDays(5));

    $this->post('/devolucao', returnForm($this->order))->assertRedirect('/devolucao');

    $request = ReturnRequest::query()->sole();
    expect($request->protocol)->toMatch('/^DEV-\d{4}-000001$/')
        ->and($request->email)->toBe('ana@example.com')
        ->and($request->within_withdrawal)->toBeTrue()
        ->and($request->status->value)->toBe('received');
    $this->get('/devolucao')->assertInertia(fn (Assert $page) => $page->where('sent.protocol', $request->protocol));

    Mail::assertQueued(ReturnReceivedMail::class, fn (ReturnReceivedMail $mail) => $mail->hasTo('ana@example.com') && $mail->protocol === $request->protocol);
    Mail::assertQueued(ReturnRequestedMail::class, function (ReturnRequestedMail $mail) use ($request) {
        $html = $mail->render();

        return $mail->hasTo('contato@tars.art.br')
            && str_contains($html, $request->protocol)
            && ! str_contains($html, 'Ana Serra') && ! str_contains($html, 'ana@example.com') && ! str_contains($html, 'não serviu');
    });
});

it('marks a request sent long after the delivery as out of the window', function () {
    deliveredOn($this->order, now()->subDays(20));

    $this->post('/devolucao', returnForm($this->order, ['reason' => 'defect']))->assertSessionHasNoErrors();

    expect(ReturnRequest::query()->sole()->within_withdrawal)->toBeFalse();
});

it('answers the same when the e-mail is not the one of the purchase or the order was never paid', function () {
    $unpaid = Orders::paidBy(null, 'ana@example.com');

    foreach ([returnForm($this->order, ['email' => 'bia@example.com']), returnForm($unpaid), returnForm($this->order, ['orderNumber' => 'OVP-2020-999999'])] as $data) {
        $this->post('/devolucao', $data)->assertSessionHasErrors(['order' => 'Não encontramos um pedido com esse número e esse e-mail.']);
    }

    expect(ReturnRequest::query()->count())->toBe(0);
    Mail::assertNothingQueued();
});

it('refuses a short message or an unknown reason in Portuguese', function () {
    $this->post('/devolucao', returnForm($this->order, ['message' => 'curta', 'reason' => 'tédio']))
        ->assertSessionHasErrors(['message' => 'Conte um pouco mais: pelo menos 10 caracteres.', 'reason' => 'Escolha um dos motivos da lista.']);

    expect(ReturnRequest::query()->count())->toBe(0);
});

it('gives a bot the usual confirmation and stores nothing', function () {
    $this->post('/devolucao', returnForm($this->order, ['website' => 'https://spam.example']))->assertRedirect('/devolucao');

    $this->get('/devolucao')->assertInertia(fn (Assert $page) => $page->where('sent.protocol', null));
    expect(ReturnRequest::query()->count())->toBe(0);
    Mail::assertNothingQueued();
});

it('limits the attempts from the same address', function () {
    foreach (range(1, 5) as $attempt) {
        $this->post('/devolucao', returnForm($this->order, ['email' => "x{$attempt}@example.com"]));
    }

    $this->post('/devolucao', returnForm($this->order))->assertStatus(429);
});

it('links the order page to the form with the number filled in', function () {
    $this->get(URL::signedRoute('order.show', ['number' => $this->order->number]))->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Orders/Show')->where('order.status', 'paid'));
    $this->get('/devolucao?pedido='.$this->order->number)->assertInertia(fn (Assert $page) => $page->where('orderNumber', $this->order->number));
});

/** A request sent through the form, as the buyer would. */
function sentReturn(object $test, Order $order): ReturnRequest
{
    $test->post('/devolucao', returnForm($order, ['email' => (string) $order->customer_email]))->assertSessionHasNoErrors();

    return ReturnRequest::query()->latest('id')->firstOrFail();
}

it('lists and opens the requests for the store role, and shows the new one on the panel home', function () {
    $request = sentReturn($this, $this->order);
    $store = Member::factory()->role('store')->create();

    $this->actingAs($store)->get('/painel/pedidos/devolucoes?status=received')->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('Panel/Returns/Index')
        ->where('items.0.protocol', $request->protocol)
        ->where('items.0.orderNumber', $this->order->number)
        ->where('counts.received', 1)
    );
    $this->get('/painel/pedidos/devolucoes?busca=nada-a-ver')->assertInertia(fn (Assert $page) => $page->where('items', []));
    $this->get("/painel/pedidos/devolucoes/{$request->protocol}")->assertInertia(fn (Assert $page) => $page
        ->component('Panel/Returns/Show')
        ->where('request.name', 'Ana Serra')
        ->where('request.next', ['in_progress', 'completed', 'declined'])
        ->where('request.order.number', $this->order->number)
    );
    $this->get('/painel')->assertInertia(fn (Assert $page) => $page
        ->where('needsYou', fn ($items) => collect($items)->contains(fn ($i) => $i['kind'] === 'return' && $i['ref'] === $request->protocol))
    );
});

it('keeps the returns away from moderators', function () {
    $request = sentReturn($this, $this->order);

    $this->actingAs(Member::factory()->role('moderator')->create());
    $this->get('/painel/pedidos/devolucoes')->assertForbidden();
    $this->get("/painel/pedidos/devolucoes/{$request->protocol}")->assertForbidden();
    $this->post("/painel/pedidos/devolucoes/{$request->protocol}/status", ['status' => 'completed', 'note' => 'Feito.'])->assertForbidden();
});

it('closes a request with a note, audited, and refuses to reopen it', function () {
    $request = sentReturn($this, $this->order);
    $store = Member::factory()->role('store')->create();
    $url = "/painel/pedidos/devolucoes/{$request->protocol}/status";

    $this->actingAs($store)->post($url, ['status' => 'completed', 'note' => ''])->assertSessionHasErrors('note');
    $this->post($url, ['status' => 'in_progress', 'note' => 'Etiqueta reversa combinada.'])->assertSessionHasNoErrors();
    $this->post($url, ['status' => 'completed', 'note' => 'Etiqueta reversa enviada e valor reembolsado'])->assertSessionHasNoErrors();
    $this->post($url, ['status' => 'in_progress', 'note' => 'Reabrir.'])->assertSessionHasErrors('note');

    expect($request->fresh()->status->value)->toBe('completed')
        ->and($request->events()->pluck('to_status')->all())->toBe(['received', 'in_progress', 'completed'])
        ->and($request->events()->get()->last()->actor_id)->toBe($store->id)
        ->and(AuditLog::query()->where('action', 'return.status_changed')->count())->toBe(2);
});

it('forgets who asked when the account is deleted, keeping protocol, reason, status and dates', function () {
    $member = Member::factory()->create(['nickname' => 'coruja', 'email' => 'coruja@serra.test']);
    $order = Orders::paidBy($member, 'coruja@serra.test', now()->subDays(3));
    $request = sentReturn($this, $order->fresh());
    expect($request->email)->toBe('coruja@serra.test');

    $this->actingAs($member)->delete('/conta', ['confirmation' => 'coruja']);

    expect($request->fresh()->only(['name', 'email', 'message']))->toBe(['name' => null, 'email' => null, 'message' => null])
        ->and($request->fresh())->protocol->toBe($request->protocol)->reason->value->toBe('withdrawal')->status->value->toBe('received');
});

it('forgets who asked when the order is anonymized after the tax term, and deletes the request with the order', function () {
    $request = sentReturn($this, $this->order);
    app(OrderRepository::class)->anonymizePaidBefore(now());

    expect($request->fresh()->only(['name', 'email', 'message']))->toBe(['name' => null, 'email' => null, 'message' => null]);

    $this->order->delete();
    expect(ReturnRequest::query()->find($request->id))->toBeNull();
});

it('exports the requests on the member orders, and only those', function () {
    $member = Member::factory()->create(['email' => 'coruja@serra.test']);
    $mine = Orders::paidBy($member, 'coruja@serra.test', now()->subDays(3));
    sentReturn($this, $mine->fresh());
    $this->post('/devolucao', returnForm($this->order))->assertSessionHasNoErrors();

    $export = app(BuildMemberExport::class)->execute($member->id);

    expect($export)->toHaveKeys(['perfil', 'relatos', 'pedidos', 'devolucoes', 'avise_me', 'consentimentos'])
        ->and($export['devolucoes'])->toHaveCount(1)
        ->and($export['devolucoes'][0])->toMatchArray(['pedido' => $mine->number, 'motivo' => 'Arrependimento']);
});

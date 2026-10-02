<?php

namespace App\Http\Controllers\Panel;

use App\Application\Orders\UseCases\OperateOrders;
use App\Domain\Orders\Contracts\ProductionDocument;
use App\Domain\Orders\InvalidOrderTransition;
use App\Domain\Orders\OrderStatus;
use App\Domain\Payments\PaymentUnavailable;
use App\Domain\Shipping\ShippingUnavailable;
use App\Http\Controllers\Controller;
use App\Http\Responses\CsvDownload;
use Closure;
use DateTimeImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** /painel/pedidos (store and admin): filters in the URL (?status=&busca=&pagina=). */
class OrderAdminController extends Controller
{
    public function index(Request $request, OperateOrders $orders): Response
    {
        $status = OrderStatus::tryFrom((string) $request->query('status'));
        $query = trim((string) $request->query('busca', ''));

        return Inertia::render('Panel/Orders/Index', [
            ...$orders->list($status, $query === '' ? null : mb_substr($query, 0, 80), (int) $request->query('pagina', 1), now()->toDateTimeImmutable()),
            'filters' => ['status' => $status?->value, 'busca' => $query],
            'period' => ['de' => now()->startOfMonth()->toDateString(), 'ate' => now()->toDateString()],
        ]);
    }

    public function show(OperateOrders $orders, string $number): Response
    {
        return Inertia::render('Panel/Orders/Show', $orders->sheet($number) ?? abort(404));
    }

    public function cpf(Request $request, OperateOrders $orders, string $number): JsonResponse
    {
        return response()->json(['cpf' => $orders->revealCpf($this->actorId($request), $number)]);
    }

    public function production(Request $request, OperateOrders $orders, string $number): RedirectResponse
    {
        return $this->run(fn () => $orders->startProduction($this->actorId($request), $number), 'Pedido em produção. O cliente foi avisado.');
    }

    public function label(Request $request, OperateOrders $orders, string $number): RedirectResponse
    {
        return $this->run(fn () => $orders->createLabel($this->actorId($request), $number), 'Etiqueta gerada.');
    }

    public function ship(Request $request, OperateOrders $orders, string $number): RedirectResponse
    {
        $data = $request->validate(['tracking' => ['nullable', 'string', 'max:40']]);

        return $this->run(fn () => $orders->markShipped($this->actorId($request), $number, $data['tracking'] ?? null), 'Pedido enviado. O cliente recebeu o rastreio.');
    }

    public function deliver(Request $request, OperateOrders $orders, string $number): RedirectResponse
    {
        return $this->run(fn () => $orders->markDelivered($this->actorId($request), $number), 'Pedido entregue.');
    }

    public function cancel(Request $request, OperateOrders $orders, string $number): RedirectResponse
    {
        $data = $request->validate(['reason' => ['required', 'string', 'max:255']], ['reason.required' => 'Registre o motivo.']);

        return $this->run(fn () => $orders->cancel($this->actorId($request), $number, $data['reason']), 'Pedido cancelado.');
    }

    public function refund(Request $request, OperateOrders $orders, string $number): RedirectResponse
    {
        $data = $request->validate(
            ['reason' => ['required', 'string', 'max:255'], 'confirm' => ['accepted']],
            ['reason.required' => 'Registre o motivo.', 'confirm.accepted' => 'Confirme o reembolso.'],
        );

        return $this->run(fn () => $orders->refund($this->actorId($request), $number, $data['reason']), 'Reembolso feito.');
    }

    public function productionPdf(OperateOrders $orders, ProductionDocument $document, string $number): HttpResponse
    {
        $sheet = $orders->sheet($number) ?? abort(404);

        return response($document->render($sheet['order']), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"producao-{$number}.pdf\"",
        ]);
    }

    public function export(Request $request, OperateOrders $orders): StreamedResponse
    {
        $data = $request->validate(['de' => ['required', 'date_format:Y-m-d'], 'ate' => ['required', 'date_format:Y-m-d', 'after_or_equal:de']]);
        $rows = $orders->export(new DateTimeImmutable($data['de']), new DateTimeImmutable($data['ate']));
        $header = $rows === [] ? ['numero'] : array_keys($rows[0]);

        return CsvDownload::make("pedidos-{$data['de']}-a-{$data['ate']}.csv", [$header, ...array_map('array_values', $rows)]);
    }

    private function run(Closure $action, string $done): RedirectResponse
    {
        try {
            $action();
        } catch (InvalidOrderTransition|InvalidArgumentException|PaymentUnavailable|ShippingUnavailable $e) {
            throw ValidationException::withMessages(['order' => $e->getMessage()]);
        }

        return back()->with('toast', $done);
    }

    private function actorId(Request $request): int
    {
        return (int) $request->user()?->getAuthIdentifier();
    }
}

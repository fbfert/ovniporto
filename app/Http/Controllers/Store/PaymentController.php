<?php

namespace App\Http\Controllers\Store;

use App\Application\Payments\UseCases\PayOrder;
use App\Domain\Orders\Contracts\OrderRepository;
use App\Domain\Orders\OrderStatus;
use App\Domain\Payments\Contracts\PaymentGateway;
use App\Domain\Payments\PaymentUnavailable;
use App\Http\Controllers\Controller;
use App\Http\Seo\ContentSeo;
use App\Http\Support\OrderAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Inertia\Inertia;
use Inertia\Response;
use InvalidArgumentException;

/**
 * The provider's buttons call these two endpoints. Neither takes an amount
 * from the browser, and no card data ever arrives here.
 */
class PaymentController extends Controller
{
    public function start(Request $request, OrderRepository $orders, PayOrder $pay, string $number): JsonResponse
    {
        $this->authorizeOrder($request, $orders, $number);

        try {
            return response()->json(['id' => $pay->start($number)]);
        } catch (PaymentUnavailable|InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function approve(Request $request, OrderRepository $orders, PayOrder $pay, string $number): JsonResponse
    {
        $this->authorizeOrder($request, $orders, $number);

        try {
            $status = $pay->approve($number);
        } catch (PaymentUnavailable|InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'status' => $status->value,
            'paid' => $status === OrderStatus::Paid,
            'redirect' => URL::signedRoute('order.show', ['number' => $number]),
        ]);
    }

    /** PayPal → us. Rejected unless the provider confirms the signature; the order is untouched then. */
    public function webhook(Request $request, PaymentGateway $gateway, PayOrder $pay): JsonResponse
    {
        $headers = array_map(fn ($values) => (string) ($values[0] ?? ''), $request->headers->all());
        $notice = $gateway->verifyWebhook($headers, $request->getContent());
        if ($notice === null) {
            return response()->json(['message' => 'invalid signature'], 400);
        }
        $pay->confirmFromWebhook($notice);

        return response()->json(['ok' => true]);
    }

    public function show(Request $request, OrderRepository $orders, ContentSeo $seo, string $number): Response
    {
        $page = $orders->page($number) ?? abort(404);
        abort_unless(OrderAccess::allows($request, $number, $page['memberId']), 403);

        return Inertia::render('Orders/Show', [
            'seo' => $seo->order($number)->toArray(),
            'order' => $page,
            'payUrl' => $page['status'] === OrderStatus::PendingPayment->value ? URL::signedRoute('order.pay', ['number' => $number]) : null,
        ]);
    }

    private function authorizeOrder(Request $request, OrderRepository $orders, string $number): void
    {
        $order = $orders->findByNumber($number) ?? abort(404);
        abort_unless(OrderAccess::allows($request, $number, $order['memberId']), 403);
    }
}

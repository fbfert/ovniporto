<?php

namespace App\Http\Controllers\Store;

use App\Application\Orders\UseCases\RequestReturn;
use App\Domain\Orders\OrderNumber;
use App\Domain\Orders\ReturnReason;
use App\Domain\Orders\ReturnRefused;
use App\Http\Controllers\Controller;
use App\Http\Requests\RequestReturnRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

/** "/devolucao": the public return and withdrawal form. */
class ReturnController extends Controller
{
    /** Session key of the confirmation shown once after sending. */
    private const SENT = 'returnRequest';

    public function show(Request $request): Response
    {
        $number = $request->string('pedido')->upper()->toString();

        return Inertia::render('Store/Return', [
            'orderNumber' => OrderNumber::isValid($number) ? $number : '',
            'reasons' => array_map(fn (ReturnReason $r) => ['value' => $r->value, 'label' => $r->label()], ReturnReason::cases()),
            'sent' => $request->session()->get(self::SENT),
        ]);
    }

    /** A filled honeypot gets the same confirmation as a person, and nothing is stored or sent. */
    public function store(RequestReturnRequest $request, RequestReturn $requestReturn): RedirectResponse
    {
        $sent = ['protocol' => null];
        if (! $request->isBot()) {
            try {
                $sent = $requestReturn->execute(
                    $request->string('orderNumber')->toString(),
                    $request->string('email')->toString(),
                    $request->string('name')->toString(),
                    $request->reason(),
                    $request->string('message')->toString(),
                    now()->toDateTimeImmutable(),
                );
            } catch (ReturnRefused $e) {
                throw ValidationException::withMessages(['order' => $e->getMessage()]);
            }
        }

        return redirect()->route('returns.create')->with(self::SENT, ['protocol' => $sent['protocol']]);
    }
}

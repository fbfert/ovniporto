<?php

namespace App\Infrastructure\Mail;

use App\Domain\Orders\Contracts\ReturnNotifier;
use App\Mail\ReturnReceivedMail;
use App\Mail\ReturnRequestedMail;
use App\Models\ReturnRequest;
use Illuminate\Support\Facades\Mail;

/**
 * Both e-mails go through the queue. The team's goes to the returns address of /painel/coordenadas
 * (RETURNS_EMAIL by default) and carries only the protocol: the details stay behind the panel's login.
 */
final class MailReturnNotifier implements ReturnNotifier
{
    public function received(string $protocol, string $buyerEmail): void
    {
        $request = ReturnRequest::query()->with('order:id,number')->where('protocol', $protocol)->first();
        if ($request === null) {
            return;
        }

        Mail::to($buyerEmail)->queue(new ReturnReceivedMail($protocol, $request->order->number, $request->reason->label(), $request->within_withdrawal));

        $team = config('ovniporto.returns_email');
        if (is_string($team) && $team !== '') {
            Mail::to($team)->queue(new ReturnRequestedMail($protocol, route('panel.orders.returns.show', ['protocol' => $protocol])));
        }
    }
}

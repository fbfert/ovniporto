<?php

namespace App\Http\Controllers;

use App\Application\Campaign\UseCases\ConfirmWaitlistSubscription;
use App\Application\Campaign\UseCases\SubscribeToWaitlist;
use App\Http\Requests\SubscribeWaitlistRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class WaitlistController extends Controller
{
    public function store(SubscribeWaitlistRequest $request, SubscribeToWaitlist $subscribe): RedirectResponse
    {
        $subscribe->execute($request->string('email')->toString(), $request->string('source', 'home')->toString());

        return back()->with('toast', 'Quase lá: confirme no seu e-mail.');
    }

    public function confirm(int $subscriber, ConfirmWaitlistSubscription $confirm): Response
    {
        abort_unless($confirm->execute($subscriber), 404);

        return Inertia::render('Waitlist/Confirmed');
    }
}

<?php

namespace App\Http\Controllers\Privacy;

use App\Application\Privacy\UseCases\Unsubscribe;
use App\Domain\Privacy\MailingList;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The link at the bottom of the Avise-me and collaboration e-mails. Signed and permanent, no
 * sign-in. Opening it only shows what will be canceled (mail scanners open links); the POST
 * from the page cancels.
 */
class UnsubscribeController extends Controller
{
    public function show(Request $request, Unsubscribe $unsubscribe, string $list, int $id): Response
    {
        $mailingList = MailingList::from($list);
        $done = $request->session()->get('unsubscribed') === true;

        return Inertia::render('Privacy/Unsubscribe', [
            'list' => $mailingList->value,
            'email' => $done ? null : $unsubscribe->preview($mailingList, $id),
            'done' => $done,
        ]);
    }

    public function store(Request $request, Unsubscribe $unsubscribe, string $list, int $id): RedirectResponse
    {
        $unsubscribe->execute(MailingList::from($list), $id);

        return redirect()->to($request->fullUrl())->with('unsubscribed', true);
    }
}

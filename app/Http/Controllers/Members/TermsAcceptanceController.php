<?php

namespace App\Http\Controllers\Members;

use App\Application\Privacy\UseCases\AcceptCurrentTerms;
use App\Application\Privacy\UseCases\CurrentTermsVersion;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/** The terms changed since the member last agreed: show the new date and ask again. */
class TermsAcceptanceController extends Controller
{
    public function show(Request $request, AcceptCurrentTerms $terms, CurrentTermsVersion $version): Response|RedirectResponse
    {
        if (! $terms->pending((int) $request->user()?->getAuthIdentifier())) {
            return redirect()->route('account');
        }

        return Inertia::render('Members/AcceptTerms', ['version' => $version->execute()]);
    }

    public function store(Request $request, AcceptCurrentTerms $terms): RedirectResponse
    {
        $request->validate(['terms' => ['accepted']], ['terms.accepted' => 'Marque que leu e aceita para continuar.']);
        $terms->execute((int) $request->user()?->getAuthIdentifier());

        return redirect()->intended(route('account'));
    }
}

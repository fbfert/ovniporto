<?php

namespace App\Http\Controllers\Members;

use App\Application\Privacy\UseCases\AcceptCurrentTerms;
use App\Application\Privacy\UseCases\CurrentTermsVersion;
use App\Http\Controllers\Controller;
use App\Http\Requests\CompleteProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

/** The terms changed or the age was never declared: ask only for what is pending, on one screen. */
class TermsAcceptanceController extends Controller
{
    public function show(Request $request, AcceptCurrentTerms $terms, CurrentTermsVersion $version): Response|RedirectResponse
    {
        $pending = $terms->pending((int) $request->user()?->getAuthIdentifier());
        if ($pending === []) {
            return redirect()->route('account');
        }

        return Inertia::render('Members/AcceptTerms', ['version' => $version->execute(), 'pending' => $pending]);
    }

    private const MESSAGES = [
        AcceptCurrentTerms::TERMS => 'Marque que leu e aceita para continuar.',
        AcceptCurrentTerms::AGE => CompleteProfileRequest::AGE_MESSAGE,
    ];

    public function store(Request $request, AcceptCurrentTerms $terms): RedirectResponse
    {
        $memberId = (int) $request->user()?->getAuthIdentifier();
        $missing = $terms->missing($memberId, $request->boolean('terms'), $request->boolean('age'));
        if ($missing !== []) {
            throw ValidationException::withMessages(array_intersect_key(self::MESSAGES, array_flip($missing)));
        }
        $terms->execute($memberId, $request->boolean('terms'), $request->boolean('age'));

        return redirect()->intended(route('account'));
    }
}

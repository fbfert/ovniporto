<?php

namespace App\Http\Controllers\Members;

use App\Application\Privacy\UseCases\AcceptCurrentTerms;
use App\Application\Privacy\UseCases\CurrentTermsVersion;
use App\Domain\Members\AgeNotDeclared;
use App\Domain\Members\TermsNotAccepted;
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

    public function store(Request $request, AcceptCurrentTerms $terms): RedirectResponse
    {
        try {
            $terms->execute((int) $request->user()?->getAuthIdentifier(), $request->boolean('terms'), $request->boolean('age'));
        } catch (TermsNotAccepted) {
            throw ValidationException::withMessages(['terms' => 'Marque que leu e aceita para continuar.']);
        } catch (AgeNotDeclared) {
            throw ValidationException::withMessages(['age' => CompleteProfileRequest::AGE_MESSAGE]);
        }

        return redirect()->intended(route('account'));
    }
}

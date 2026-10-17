<?php

namespace App\Http\Controllers\Members;

use App\Application\Members\UseCases\SignInWithIdentity;
use App\Domain\Members\Contracts\IdentityProvider;
use App\Domain\Members\Contracts\IdTokenVerifier;
use App\Domain\Members\Data\Identity;
use App\Http\Controllers\Controller;
use App\Http\Middleware\TouchLastSeen;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Throwable;

class GoogleAuthController extends Controller
{
    /** Where guests land when a page needs an account (the "login" route). */
    public function show(): Response
    {
        return Inertia::render('Members/SignIn');
    }

    public function redirect(IdentityProvider $provider): HttpResponse
    {
        return Inertia::location($provider->redirectUrl());
    }

    public function callback(Request $request, IdentityProvider $provider, SignInWithIdentity $signIn): RedirectResponse
    {
        try {
            $identity = $provider->identity();
        } catch (Throwable $e) {
            report($e);

            return redirect()->route('home')->with('toast', 'Não deu para entrar com o Google. Tente de novo.');
        }

        return $this->signInAndRedirect($request, $signIn, $identity);
    }

    /** The Android app signs in natively and posts the Google ID token here to open the same session. */
    public function token(Request $request, IdTokenVerifier $verifier, SignInWithIdentity $signIn): RedirectResponse
    {
        $validated = $request->validate(['id_token' => ['required', 'string']]);

        try {
            $identity = $verifier->verify($validated['id_token']);
        } catch (Throwable $e) {
            report($e);

            return redirect()->route('home')->with('toast', 'Não deu para entrar com o Google. Tente de novo.');
        }

        return $this->signInAndRedirect($request, $signIn, $identity);
    }

    private function signInAndRedirect(Request $request, SignInWithIdentity $signIn, Identity $identity): RedirectResponse
    {
        $result = $signIn->execute($identity);
        Auth::loginUsingId($result['memberId'], remember: true);
        $request->session()->regenerate();
        // The sign-in already wrote today's last access.
        $request->session()->put(TouchLastSeen::SESSION_KEY, now()->toDateString());

        return $result['isNew']
            ? redirect()->route('welcome')
            : redirect()->intended(route('account'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('toast', 'Até a próxima vigília.');
    }
}

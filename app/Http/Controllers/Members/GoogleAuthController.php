<?php

namespace App\Http\Controllers\Members;

use App\Application\Members\UseCases\SignInWithIdentity;
use App\Domain\Members\Contracts\IdentityProvider;
use App\Http\Controllers\Controller;
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

        $result = $signIn->execute($identity);
        Auth::loginUsingId($result['memberId']);
        $request->session()->regenerate();

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

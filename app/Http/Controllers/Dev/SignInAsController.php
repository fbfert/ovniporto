<?php

namespace App\Http\Controllers\Dev;

use App\Http\Controllers\Controller;
use App\Models\Member;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Local development only: sign in as a member without Google, to try the
 * member screens before OAuth credentials exist. 404 in any other environment.
 */
class SignInAsController extends Controller
{
    public function __invoke(Request $request, int $member): RedirectResponse
    {
        abort_unless(app()->isLocal(), 404);

        Auth::login(Member::query()->findOrFail($member));
        $request->session()->regenerate();

        return redirect()->route('account');
    }
}

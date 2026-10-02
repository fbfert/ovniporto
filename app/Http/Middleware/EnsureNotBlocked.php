<?php

namespace App\Http\Middleware;

use App\Domain\Members\MemberBlocked;
use App\Models\Member;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Keeps a blocked member out of the report wizard (the use cases refuse the send too). */
class EnsureNotBlocked
{
    public function handle(Request $request, Closure $next): Response
    {
        $member = $request->user();
        if ($member instanceof Member && $member->isBlocked()) {
            $message = (new MemberBlocked)->getMessage();
            abort_unless($request->isMethod('GET'), 403, $message);

            return redirect()->route('account')->with('toast', $message);
        }

        return $next($request);
    }
}

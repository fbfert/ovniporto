<?php

namespace App\Http\Middleware;

use App\Models\Member;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Members without a nickname or the accepted terms go to the welcome screen first. */
class EnsureProfileCompleted
{
    public function handle(Request $request, Closure $next): Response
    {
        $member = $request->user();
        if ($member instanceof Member && ! $member->hasCompleteProfile()) {
            return redirect()->route('welcome');
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use App\Application\Privacy\UseCases\AcceptCurrentTerms;
use App\Models\Member;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Members without a nickname or the accepted terms go to the welcome screen first;
 * when the terms change, they accept the new version before going on.
 */
class EnsureProfileCompleted
{
    public function __construct(private AcceptCurrentTerms $terms) {}

    public function handle(Request $request, Closure $next): Response
    {
        $member = $request->user();
        if (! $member instanceof Member) {
            return $next($request);
        }
        if (! $member->hasCompleteProfile()) {
            return redirect()->route('welcome');
        }
        if ($this->terms->pending($member->id)) {
            if ($request->isMethod('GET')) {
                redirect()->setIntendedUrl($request->fullUrl());
            }

            return redirect()->route('terms.accept');
        }

        return $next($request);
    }
}

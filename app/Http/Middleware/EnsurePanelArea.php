<?php

namespace App\Http\Middleware;

use App\Models\Member;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

/** `panel:relatos`: the signed-in member's role must open that panel area (see PanelArea). */
class EnsurePanelArea
{
    public function handle(Request $request, Closure $next, string $area): Response
    {
        $member = $request->user();
        abort_unless($member instanceof Member && Gate::forUser($member)->allows('panel', $area), 403);

        return $next($request);
    }
}

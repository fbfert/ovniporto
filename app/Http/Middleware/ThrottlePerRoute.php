<?php

namespace App\Http\Middleware;

use Illuminate\Routing\Middleware\ThrottleRequests;

/**
 * "throttle:N,M" with one counter per route. Laravel's own middleware keys every
 * unnamed limit by the member or the IP alone, so the cart and the shipping quote
 * would spend the attempts of the waitlist, the return form and the report.
 */
class ThrottlePerRoute extends ThrottleRequests
{
    protected function resolveRequestSignature($request)
    {
        $route = $request->route();
        $scope = $route?->getName() ?? $route?->uri() ?? $request->path();

        return sha1($scope.'|'.parent::resolveRequestSignature($request));
    }
}

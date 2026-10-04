<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Pages and Inertia visits carry an ETag of their body and "private, no-cache": the
 * browser always asks again and gets a bodyless 304 when nothing changed (cheap on a
 * weak 4G signal). Private, so no shared cache ever keeps a page with session data.
 * Responses that chose their own caching (public API, images, files) keep it.
 */
class RevalidateWithETag
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $request->isMethodCacheable() || $response->getStatusCode() !== 200
            || $response instanceof BinaryFileResponse || $response instanceof StreamedResponse
            || $response->headers->hasCacheControlDirective('public')
            || $response->headers->hasCacheControlDirective('no-store')) {
            return $response;
        }

        $response->headers->set('Cache-Control', 'private, no-cache');
        $response->setEtag(hash('xxh128', (string) $response->getContent()));
        $response->isNotModified($request);

        return $response;
    }
}

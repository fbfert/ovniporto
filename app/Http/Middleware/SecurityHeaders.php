<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Edge headers on every web response: Content-Security-Policy allowing only what the
 * site uses (PayPal, map tiles, our own metric, the 3D viewers of /o-lugar), frame and
 * type protections, and HSTS on HTTPS. CSP mode comes from config("ovniporto.csp"):
 * "enforce" (production), "report-only" (staging, violations go to /csp-report) or
 * "off" (local development, where Vite injects inline scripts).
 */
class SecurityHeaders
{
    /** Admin tools that render inline scripts of their own (Horizon, Pulse/Livewire). */
    private const ADMIN_TOOLS = ['painel/filas', 'painel/filas/*', 'painel/saude', 'painel/saude/*'];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $headers = $response->headers;

        $headers->set('X-Frame-Options', 'DENY');
        $headers->set('X-Content-Type-Options', 'nosniff');
        $headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        // Geolocation only for "usar minha localização" in the report; nothing else.
        $headers->set('Permissions-Policy', 'geolocation=(self), camera=(), microphone=(), payment=(self "https://www.paypal.com")');

        if ($request->isSecure()) {
            $headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        $mode = (string) config('ovniporto.csp');
        if ($mode !== 'off') {
            $name = $mode === 'report-only' ? 'Content-Security-Policy-Report-Only' : 'Content-Security-Policy';
            $headers->set($name, $this->policy($request));
        }

        return $response;
    }

    private function policy(Request $request): string
    {
        $paypal = ['https://*.paypal.com', 'https://*.paypalobjects.com'];
        $metric = array_filter([self::origin(config('services.umami.script_url'))]);
        $embeds = array_map(fn (string $host) => "https://{$host}", (array) config('ovniporto.embed_hosts'));
        $adminTool = $request->is(...self::ADMIN_TOOLS);

        $directives = [
            'default-src' => ["'self'"],
            'script-src' => ["'self'", ...$paypal, ...$metric, ...($adminTool ? ["'unsafe-inline'", "'unsafe-eval'"] : [])],
            // React and the PayPal SDK set inline style attributes.
            'style-src' => ["'self'", "'unsafe-inline'"],
            'img-src' => ["'self'", 'data:', 'blob:', 'https://tile.openstreetmap.org', ...$paypal],
            'font-src' => ["'self'"],
            'connect-src' => ["'self'", ...$paypal, ...$metric],
            // Origin videos load only after a click. Allowed site-wide because Inertia keeps the CSP of
            // the first document across client-side visits.
            'frame-src' => [...$paypal, 'https://www.youtube-nocookie.com', ...$embeds],
            'worker-src' => ["'self'"],
            'manifest-src' => ["'self'"],
            'object-src' => ["'none'"],
            'base-uri' => ["'self'"],
            'form-action' => ["'self'"],
            'frame-ancestors' => ["'none'"],
            'report-uri' => ['/csp-report'],
        ];

        return implode('; ', array_map(
            fn (string $name, array $sources) => $name.' '.implode(' ', array_unique($sources)),
            array_keys($directives),
            $directives,
        ));
    }

    private static function origin(mixed $url): ?string
    {
        $parts = is_string($url) ? parse_url($url) : false;

        return $parts && isset($parts['scheme'], $parts['host'])
            ? "{$parts['scheme']}://{$parts['host']}".(isset($parts['port']) ? ":{$parts['port']}" : '')
            : null;
    }
}

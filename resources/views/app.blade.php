<!DOCTYPE html>
<html lang="pt-BR">
    <head>
        @php($seo = $page['props']['seo'] ?? null)
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="theme-color" content="#061121">
        {{-- The only render-blocking file goes first, ahead of the JS module preloads (they would queue it on 4G). --}}
        @vite('resources/css/app.css')
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/icons/apple-touch-icon.png">
        <link rel="manifest" href="/manifest.webmanifest">
        {{-- Self-hosted (resources/css/fonts.css): the body text and the titles of the first screen. --}}
        <link rel="preload" href="/fonts/figtree-latin-400-normal.woff2" as="font" type="font/woff2" crossorigin>
        <link rel="preload" href="/fonts/unbounded-latin-800-normal.woff2" as="font" type="font/woff2" crossorigin>
        @viteReactRefresh
        @vite(['resources/js/app.tsx', "resources/js/Pages/{$page['component']}.tsx"])
        @if (is_array($seo))
            @include('partials.seo', ['seo' => $seo])
        @endif
        @include('partials.analytics')
        {{-- With SSR on, the rendered <title> from SeoHead replaces this fallback. --}}
        <x-inertia::head>
            <title>{{ is_array($seo) ? $seo['fullTitle'] : 'OVNIPORTO Lages' }}</title>
        </x-inertia::head>
    </head>
    <body>
        <x-inertia::app />
    </body>
</html>

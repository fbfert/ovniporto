<!DOCTYPE html>
<html lang="pt-BR">
    <head>
        @php($seo = $page['props']['seo'] ?? null)
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="theme-color" content="#061121">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Caveat:wght@600&family=Figtree:wght@400;500;600&family=Unbounded:wght@700;800&display=swap">
        @viteReactRefresh
        @vite(['resources/css/app.css', 'resources/js/app.tsx', "resources/js/Pages/{$page['component']}.tsx"])
        @if (is_array($seo))
            @include('partials.seo', ['seo' => $seo])
        @endif
        {{-- With SSR on, the rendered <title> from SeoHead replaces this fallback. --}}
        <x-inertia::head>
            <title>{{ is_array($seo) ? $seo['fullTitle'] : 'OVNIPORTO Lages' }}</title>
        </x-inertia::head>
    </head>
    <body>
        <x-inertia::app />
    </body>
</html>

import '../css/app.css';

import { createInertiaApp } from '@inertiajs/react';
import { createRoot, hydrateRoot } from 'react-dom/client';
import { resolvePage } from '@/lib/pages';

// Only the offline page "Sem sinal da torre" (public/sw.js); never registered by the dev server.
if (import.meta.env.PROD && 'serviceWorker' in navigator) {
    window.addEventListener('load', () => void navigator.serviceWorker.register('/sw.js'));
}

createInertiaApp({
    title: (title) => (title ? `${title} · OVNIPORTO Lages` : 'OVNIPORTO Lages · A pista de pouso do planalto'),
    resolve: resolvePage,
    setup({ el, App, props }) {
        if (el.hasChildNodes()) {
            hydrateRoot(el, <App {...props} />);
            return;
        }
        createRoot(el).render(<App {...props} />);
    },
    progress: {
        color: '#54C933',
        showSpinner: false,
    },
    defaults: {
        // A 150 ms cross-fade between pages (resources/css/base.css); partial reloads such as
        // filters and "load more" keep the page and skip it. Inertia restores the scroll on back.
        visitOptions: (_href, options) => ({
            ...options,
            viewTransition:
                options.viewTransition ||
                (options.method === 'get' && !options.preserveState && (options.only?.length ?? 0) === 0),
        }),
    },
});

import '../css/app.css';

import { createInertiaApp } from '@inertiajs/react';
import { createRoot, hydrateRoot } from 'react-dom/client';
import { resolvePage } from '@/lib/pages';

createInertiaApp({
    title: (title) => (title ? `${title} · OVNIPORTO Lages` : 'OVNIPORTO Lages'),
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
});

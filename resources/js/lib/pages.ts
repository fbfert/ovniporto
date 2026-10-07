import type { ResolvedComponent } from '@inertiajs/react';

type PageModule = { default: ResolvedComponent };

// Tests live next to their pages; the negative pattern keeps them out of the client and SSR builds.
const pages = import.meta.glob<PageModule>(['../Pages/**/*.tsx', '!../Pages/**/*.test.tsx']);

export async function resolvePage(name: string): Promise<ResolvedComponent> {
    const loader = pages[`../Pages/${name}.tsx`];
    if (!loader) {
        throw new Error(`Page not found: ${name}`);
    }
    return (await loader()).default;
}

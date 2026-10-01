import type { ResolvedComponent } from '@inertiajs/react';

type PageModule = { default: ResolvedComponent };

const pages = import.meta.glob<PageModule>('../Pages/**/*.tsx');

export async function resolvePage(name: string): Promise<ResolvedComponent> {
    const loader = pages[`../Pages/${name}.tsx`];
    if (!loader) {
        throw new Error(`Page not found: ${name}`);
    }
    return (await loader()).default;
}

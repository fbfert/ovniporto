import { Head, usePage } from '@inertiajs/react';
import type { SharedProps } from '@/types';

/**
 * Keeps the tab title in step with client-side navigation. The description, canonical,
 * Open Graph and JSON-LD tags come from the server (app.blade.php) on every full load,
 * so link previews never depend on JavaScript or on the SSR process.
 */
export function SeoHead() {
    const { seo } = usePage<SharedProps>().props;

    return <Head title={seo?.title ?? undefined} />;
}

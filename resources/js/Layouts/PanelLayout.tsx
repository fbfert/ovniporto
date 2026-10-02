import { Link, usePage } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { Seal } from '@/Components/Brand/Seal';
import { FlashToasts, ToastProvider } from '@/Components/Ui/Toast';
import { t } from '@/i18n/pt-BR';
import type { SharedProps } from '@/types';

const copy = t.panel;

const hrefOf = (area: string) => (area === 'inicio' ? '/painel' : `/painel/${area}`);

/** Which area the current URL belongs to: "/painel/relatos/12" → "relatos". */
function activeArea(url: string): string {
    const segment = url.split('?')[0]?.split('/')[2];
    return segment && segment.length > 0 ? segment : 'inicio';
}

/**
 * The operations panel: a night bar with the seal and the areas this role may
 * open (the server checks every one again), a moonlight work surface below.
 * No decorative motion here: operators come back to it all day.
 */
export function PanelLayout({ children }: { children: ReactNode }) {
    const page = usePage<SharedProps>();
    const { panelAreas } = page.props;
    const current = activeArea(page.url);

    return (
        <ToastProvider>
            <FlashToasts />
            <header data-tone="dark" className="sticky top-0 z-40 bg-night text-moonlight">
                <div className="mx-auto flex max-w-6xl items-center gap-3 px-4 pt-3 sm:px-6">
                    <Link href="/painel" className="flex min-h-11 items-center gap-3" aria-label={copy.title}>
                        <Seal size="sm" />
                        <span className="leading-tight">
                            <span className="block font-script text-lg text-beam-glow">{copy.eyebrow}</span>
                            <span className="block font-display text-sm font-extrabold tracking-[0.06em] uppercase">
                                {copy.title}
                            </span>
                        </span>
                    </Link>
                    <a
                        href="/"
                        className="ml-auto inline-flex min-h-11 shrink-0 items-center rounded-full px-4 text-sm font-semibold whitespace-nowrap text-moonlight/80 ring-1 ring-moonlight/25 hover:text-moonlight hover:ring-moonlight/50"
                    >
                        {copy.backToSite}
                    </a>
                </div>
                <nav aria-label={copy.menuLabel} className="mx-auto max-w-6xl overflow-x-auto px-4 pt-3 pb-3 sm:px-6">
                    <ul className="flex w-max gap-1.5">
                        {(panelAreas ?? []).map((area) => (
                            <li key={area}>
                                <Link
                                    href={hrefOf(area)}
                                    aria-current={area === current ? 'page' : undefined}
                                    className="inline-flex min-h-11 items-center rounded-full px-4 text-sm font-semibold text-moonlight/75 hover:bg-moonlight/8 hover:text-moonlight aria-[current=page]:bg-beam aria-[current=page]:text-night"
                                >
                                    {copy.areas[area] ?? area}
                                </Link>
                            </li>
                        ))}
                    </ul>
                </nav>
            </header>
            <main id="conteudo" className="min-h-[calc(100svh-8rem)] bg-moonlight text-night">
                <div className="mx-auto max-w-6xl px-4 py-8 sm:px-6 sm:py-12">{children}</div>
            </main>
        </ToastProvider>
    );
}

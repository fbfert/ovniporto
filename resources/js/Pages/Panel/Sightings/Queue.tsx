import { Head, Link } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { QueueList, type QueueItem } from '@/Components/Panel/QueueList';
import { Display } from '@/Components/Ui/Typography';
import { t } from '@/i18n/pt-BR';
import { PanelLayout } from '@/Layouts/PanelLayout';

const copy = t.panel.queue;
const TABS = ['pendentes', 'ajuste', 'aprovados', 'rejeitados'] as const;

interface Props {
    tab: string;
    counts: Record<string, number>;
    items: QueueItem[];
    page: number;
    hasMore: boolean;
}

const pageHref = (tab: string, page: number) => `/painel/relatos?aba=${tab}${page > 1 ? `&pagina=${page}` : ''}`;

export default function Queue({ tab, counts, items, page, hasMore }: Props) {
    return (
        <>
            <Head title={`${copy.title} · ${t.panel.title}`} />
            <Display as="h1" className="text-[clamp(1.8rem,1.2rem+2.4vw,2.8rem)]!">
                {copy.title}
            </Display>

            <nav aria-label={copy.tabsLabel} className="-mx-4 mt-8 overflow-x-auto px-4">
                <ul className="flex w-max gap-2">
                    {TABS.map((key) => (
                        <li key={key}>
                            <Link
                                href={pageHref(key, 1)}
                                aria-current={key === tab ? 'page' : undefined}
                                preserveScroll
                                className="inline-flex min-h-11 items-center gap-2 rounded-full border-[1.5px] border-night/25 px-4 text-[0.95rem] font-semibold text-night/80 hover:border-night/50 aria-[current=page]:border-night aria-[current=page]:bg-night aria-[current=page]:text-moonlight"
                            >
                                {copy.tabs[key]}
                                <span className="rounded-full bg-current/12 px-2 py-0.5 text-xs tabular-nums">
                                    {counts[key] ?? 0}
                                </span>
                            </Link>
                        </li>
                    ))}
                </ul>
            </nav>

            <div className="mt-8">
                {items.length === 0 ? (
                    <p className="rounded-[22px] border-2 border-dashed border-night/20 p-10 text-center font-script text-2xl text-horizon">
                        {copy.empty[tab]}
                    </p>
                ) : (
                    <>
                        <QueueList items={items} />
                        <p className="mt-4 hidden text-sm text-night/60 sm:block">{copy.shortcuts}</p>
                    </>
                )}
            </div>

            {(page > 1 || hasMore) && (
                <nav className="mt-8 flex justify-between gap-3" aria-label={t.panel.pagesLabel}>
                    {page > 1 ? (
                        <Link
                            href={pageHref(tab, page - 1)}
                            className="min-h-11 font-semibold underline underline-offset-4"
                        >
                            {copy.newer}
                        </Link>
                    ) : (
                        <span />
                    )}
                    {hasMore && (
                        <Link
                            href={pageHref(tab, page + 1)}
                            className="min-h-11 font-semibold underline underline-offset-4"
                        >
                            {copy.older}
                        </Link>
                    )}
                </nav>
            )}
        </>
    );
}

Queue.layout = (page: ReactNode) => <PanelLayout>{page}</PanelLayout>;

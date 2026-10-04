import { Head, Link, router } from '@inertiajs/react';
import { useEffect, useRef, useState, type ReactNode } from 'react';
import { PanelSection } from '@/Components/Panel/PanelSection';
import { TextField } from '@/Components/Ui/Fields';
import { Badge, Display } from '@/Components/Ui/Typography';
import { money, t } from '@/i18n/pt-BR';
import { PanelLayout } from '@/Layouts/PanelLayout';

const copy = t.panel.orders;
const STATUSES = ['paid', 'in_production', 'shipped', 'pending_payment', 'delivered', 'canceled', 'refunded'];

interface Row {
    number: string;
    status: string;
    customer: string | null;
    items: number;
    totalCents: number;
    payment: string | null;
    pickup: boolean;
    createdAt: string;
    ageHours: number;
}

interface Props {
    items: Row[];
    total: number;
    counts: Record<string, number>;
    page: number;
    hasMore: boolean;
    filters: { status: string | null; busca: string };
    period: { de: string; ate: string };
}

function visit(filters: Props['filters'], page = 1) {
    const query: Record<string, string> = {};
    if (filters.status) query.status = filters.status;
    if (filters.busca) query.busca = filters.busca;
    if (page > 1) query.pagina = String(page);
    router.get('/painel/pedidos', query, { preserveState: true, preserveScroll: true, replace: true });
}

export default function Index({ items, total, counts, page, hasMore, filters, period }: Props) {
    const [search, setSearch] = useState(filters.busca);
    const timer = useRef<number | undefined>(undefined);
    useEffect(() => () => window.clearTimeout(timer.current), []);

    return (
        <>
            <Head title={`${copy.title} · ${t.panel.title}`} />
            <Display as="h1" className="text-[clamp(1.8rem,1.2rem+2.4vw,2.8rem)]!">
                {copy.title}
            </Display>

            <nav aria-label={t.order.timeline} className="-mx-4 mt-8 overflow-x-auto px-4">
                <ul className="flex w-max gap-2">
                    {[null, ...STATUSES].map((status) => (
                        <li key={status ?? 'all'}>
                            <button
                                type="button"
                                aria-current={filters.status === status ? 'page' : undefined}
                                onClick={() => visit({ ...filters, status })}
                                className="inline-flex min-h-11 items-center gap-2 rounded-full border-[1.5px] border-night/25 px-4 text-[0.95rem] font-semibold text-night/80 hover:border-night/50 aria-[current=page]:border-night aria-[current=page]:bg-night aria-[current=page]:text-moonlight"
                            >
                                {status ? t.order.status[status] : copy.all}
                                {status && (
                                    <span className="rounded-full bg-current/12 px-2 py-0.5 text-xs tabular-nums">
                                        {counts[status] ?? 0}
                                    </span>
                                )}
                            </button>
                        </li>
                    ))}
                </ul>
            </nav>

            <div className="mt-6 max-w-xl">
                <TextField
                    type="search"
                    label={copy.searchLabel}
                    value={search}
                    onChange={(e) => {
                        const value = e.target.value;
                        setSearch(value);
                        window.clearTimeout(timer.current);
                        timer.current = window.setTimeout(() => visit({ ...filters, busca: value.trim() }), 300);
                    }}
                />
            </div>

            {items.length === 0 ? (
                <p className="mt-8 rounded-[22px] border-2 border-dashed border-night/20 p-10 text-center font-script text-2xl text-horizon">
                    {copy.empty}
                </p>
            ) : (
                <ul
                    className="mt-8 divide-y-2 divide-dashed divide-night/12 border-y-2 border-dashed border-night/12"
                    aria-label={`${total}`}
                >
                    {items.map((order) => (
                        <li key={order.number}>
                            <Link
                                href={`/painel/pedidos/${order.number}`}
                                className="grid gap-1 rounded-2xl px-2 py-4 outline-none hover:bg-night/4 focus-visible:ring-2 focus-visible:ring-beam sm:grid-cols-[minmax(0,1fr)_auto] sm:items-center"
                            >
                                <span>
                                    <span className="flex flex-wrap items-center gap-2">
                                        <span className="font-display font-bold tracking-[0.03em]">{order.number}</span>
                                        <Badge tone={order.status === 'paid' ? 'car' : 'neutral'}>
                                            {t.order.status[order.status]}
                                        </Badge>
                                        {order.pickup && <Badge tone="horizon">{copy.pickup}</Badge>}
                                    </span>
                                    <span className="mt-1 block text-sm text-night/65">
                                        {order.customer ?? '—'} · {copy.items(order.items)}
                                        {order.payment && ` · ${order.payment}`}
                                    </span>
                                </span>
                                <span className="flex items-baseline gap-3 sm:justify-end">
                                    <span className="font-semibold tabular-nums">{money(order.totalCents)}</span>
                                    <span className="text-sm text-night/60">{t.panel.since(order.ageHours)}</span>
                                </span>
                            </Link>
                        </li>
                    ))}
                </ul>
            )}

            {(page > 1 || hasMore) && (
                <nav className="mt-6 flex justify-between gap-3" aria-label={t.panel.pagesLabel}>
                    {page > 1 ? (
                        <button
                            type="button"
                            onClick={() => visit(filters, page - 1)}
                            className="min-h-11 font-semibold underline underline-offset-4"
                        >
                            {t.panel.queue.newer}
                        </button>
                    ) : (
                        <span />
                    )}
                    {hasMore && (
                        <button
                            type="button"
                            onClick={() => visit(filters, page + 1)}
                            className="min-h-11 font-semibold underline underline-offset-4"
                        >
                            {t.panel.queue.older}
                        </button>
                    )}
                </nav>
            )}

            <PanelSection title={copy.exportTitle} className="mt-10 max-w-xl">
                <form method="get" action="/painel/pedidos/exportar" className="flex flex-wrap items-end gap-3">
                    <TextField type="date" name="de" label={copy.from} defaultValue={period.de} required />
                    <TextField type="date" name="ate" label={copy.to} defaultValue={period.ate} required />
                    <button
                        type="submit"
                        className="inline-flex min-h-11 items-center rounded-full border-[1.5px] border-night/70 px-5 font-semibold hover:bg-night/5"
                    >
                        {copy.exportCta}
                    </button>
                </form>
            </PanelSection>
        </>
    );
}

Index.layout = (page: ReactNode) => <PanelLayout>{page}</PanelLayout>;

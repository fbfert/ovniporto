import { Head, Link, router } from '@inertiajs/react';
import { useEffect, useRef, useState, type ReactNode } from 'react';
import { TextField } from '@/Components/Ui/Fields';
import { Badge, Display } from '@/Components/Ui/Typography';
import { t } from '@/i18n/pt-BR';
import { PanelLayout } from '@/Layouts/PanelLayout';

const copy = t.panel.returns;
const STATUSES = ['received', 'in_progress', 'completed', 'declined'];

interface Row {
    protocol: string;
    orderNumber: string;
    reason: string;
    withinWithdrawal: boolean;
    status: string;
    createdAt: string;
}

interface Props {
    items: Row[];
    counts: Record<string, number>;
    filters: { status: string | null; busca: string };
}

const day = (iso: string) =>
    new Date(iso).toLocaleDateString('pt-BR', { dateStyle: 'short', timeZone: 'America/Sao_Paulo' });

function visit(filters: Props['filters']) {
    const query: Record<string, string> = {};
    if (filters.status) query.status = filters.status;
    if (filters.busca) query.busca = filters.busca;
    router.get('/painel/pedidos/devolucoes', query, { preserveState: true, preserveScroll: true, replace: true });
}

/** /painel/pedidos/devolucoes: status chips and search in the URL, newest first. */
export default function Index({ items, counts, filters }: Props) {
    const [search, setSearch] = useState(filters.busca);
    const timer = useRef<number | undefined>(undefined);
    useEffect(() => () => window.clearTimeout(timer.current), []);

    return (
        <>
            <Head title={`${copy.title} · ${t.panel.title}`} />
            <Link
                href="/painel/pedidos"
                className="inline-flex min-h-11 items-center text-sm font-semibold underline underline-offset-4"
            >
                ← {copy.back}
            </Link>
            <Display as="h1" className="mt-2 text-[clamp(1.8rem,1.2rem+2.4vw,2.8rem)]!">
                {copy.title}
            </Display>

            <nav aria-label={copy.title} className="-mx-4 mt-8 overflow-x-auto px-4">
                <ul className="flex w-max gap-2">
                    {[null, ...STATUSES].map((status) => (
                        <li key={status ?? 'all'}>
                            <button
                                type="button"
                                aria-current={filters.status === status ? 'page' : undefined}
                                onClick={() => visit({ ...filters, status })}
                                className="inline-flex min-h-11 items-center gap-2 rounded-full border-[1.5px] border-night/25 px-4 text-[0.95rem] font-semibold text-night/80 hover:border-night/50 aria-[current=page]:border-night aria-[current=page]:bg-night aria-[current=page]:text-moonlight"
                            >
                                {status ? copy.status[status] : copy.all}
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
                <ul className="mt-8 divide-y-2 divide-dashed divide-night/12 border-y-2 border-dashed border-night/12">
                    {items.map((row) => (
                        <li key={row.protocol}>
                            <Link
                                href={`/painel/pedidos/devolucoes/${row.protocol}`}
                                className="grid gap-1 rounded-2xl px-2 py-4 outline-none hover:bg-night/4 focus-visible:ring-2 focus-visible:ring-beam sm:grid-cols-[minmax(0,1fr)_auto] sm:items-center"
                            >
                                <span>
                                    <span className="flex flex-wrap items-center gap-2">
                                        <span className="font-display font-bold tracking-[0.03em]">{row.protocol}</span>
                                        <Badge tone={row.status === 'received' ? 'car' : 'neutral'}>
                                            {copy.status[row.status]}
                                        </Badge>
                                        <Badge tone={row.withinWithdrawal ? 'beam' : 'horizon'}>
                                            {row.withinWithdrawal ? copy.within : copy.outside}
                                        </Badge>
                                    </span>
                                    <span className="mt-1 block text-sm text-night/65">
                                        {copy.order} {row.orderNumber} · {copy.reasons[row.reason]}
                                    </span>
                                </span>
                                <span className="text-sm text-night/60 tabular-nums">{day(row.createdAt)}</span>
                            </Link>
                        </li>
                    ))}
                </ul>
            )}
        </>
    );
}

Index.layout = (page: ReactNode) => <PanelLayout>{page}</PanelLayout>;

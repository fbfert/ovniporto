import { Head, Link, router } from '@inertiajs/react';
import { useEffect, useRef, useState, type ReactNode } from 'react';
import { ChipGroup, TextField } from '@/Components/Ui/Fields';
import { Badge, Display } from '@/Components/Ui/Typography';
import { shortDate, t } from '@/i18n/pt-BR';
import { PanelLayout } from '@/Layouts/PanelLayout';

const copy = t.panel.members;
const ALL = 'todos';

interface Row {
    id: number;
    nickname: string | null;
    name: string;
    email: string;
    avatarUrl: string | null;
    role: string;
    blocked: boolean;
    reports: number;
    joinedAt: string;
}

interface Filters {
    busca: string;
    papel: string | null;
    situacao: string | null;
    ordem: string;
}

interface Props {
    items: Row[];
    total: number;
    page: number;
    hasMore: boolean;
    filters: Filters;
}

const ROLE_OPTIONS = [
    { value: ALL, label: copy.allRoles },
    ...Object.entries(copy.roles).map(([value, label]) => ({ value, label })),
];
const STATUS_OPTIONS = [
    { value: ALL, label: copy.allStatus },
    { value: 'bloqueados', label: copy.blockedOnly },
];

/** Every filter is in the URL, so a filtered list can be shared with another operator. */
function visit(filters: Filters, page = 1) {
    const query: Record<string, string> = {};
    if (filters.busca) query.busca = filters.busca;
    if (filters.papel) query.papel = filters.papel;
    if (filters.situacao) query.situacao = filters.situacao;
    if (filters.ordem !== 'recentes') query.ordem = filters.ordem;
    if (page > 1) query.pagina = String(page);
    router.get('/painel/membros', query, { preserveState: true, preserveScroll: true, replace: true });
}

export default function Index({ items, total, page, hasMore, filters }: Props) {
    const [search, setSearch] = useState(filters.busca);
    const timer = useRef<number | undefined>(undefined);
    useEffect(() => () => window.clearTimeout(timer.current), []);

    // Typing searches after a short pause; the request carries the other filters along.
    const onSearch = (value: string) => {
        setSearch(value);
        window.clearTimeout(timer.current);
        timer.current = window.setTimeout(() => visit({ ...filters, busca: value.trim() }), 300);
    };

    return (
        <>
            <Head title={`${copy.title} · ${t.panel.title}`} />
            <div className="flex flex-wrap items-end justify-between gap-3">
                <Display as="h1" className="text-[clamp(1.8rem,1.2rem+2.4vw,2.8rem)]!">
                    {copy.title}
                </Display>
                <p className="font-script text-2xl text-horizon" aria-live="polite">
                    {copy.total(total)}
                </p>
            </div>

            <div className="mt-8 grid gap-5 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-end">
                <TextField
                    type="search"
                    label={copy.searchLabel}
                    value={search}
                    onChange={(e) => onSearch(e.target.value)}
                    maxLength={80}
                />
                <ChipGroup
                    label={copy.sortLabel}
                    options={copy.sorts}
                    value={filters.ordem}
                    onChange={(ordem) => visit({ ...filters, ordem })}
                />
            </div>
            <div className="mt-5 flex flex-wrap gap-x-8 gap-y-4">
                <ChipGroup
                    label={copy.roleLabel}
                    options={ROLE_OPTIONS}
                    value={filters.papel ?? ALL}
                    onChange={(papel) => visit({ ...filters, papel: papel === ALL ? null : papel })}
                />
                <ChipGroup
                    label={copy.statusLabel}
                    options={STATUS_OPTIONS}
                    value={filters.situacao ?? ALL}
                    onChange={(situacao) => visit({ ...filters, situacao: situacao === ALL ? null : situacao })}
                />
            </div>

            {items.length === 0 ? (
                <p className="mt-10 rounded-[22px] border-2 border-dashed border-night/20 p-10 text-center font-script text-2xl text-horizon">
                    {copy.empty}
                </p>
            ) : (
                <ul className="mt-10 divide-y-2 divide-dashed divide-night/12 border-y-2 border-dashed border-night/12">
                    {items.map((member) => (
                        <li key={member.id}>
                            <Link
                                href={`/painel/membros/${member.id}`}
                                className="grid grid-cols-[2.75rem_minmax(0,1fr)] items-center gap-4 rounded-2xl px-2 py-4 outline-none hover:bg-night/4 focus-visible:ring-2 focus-visible:ring-beam sm:grid-cols-[2.75rem_minmax(0,1fr)_auto]"
                            >
                                <span className="flex size-11 items-center justify-center overflow-hidden rounded-full bg-night font-display text-sm font-bold text-beam-glow uppercase">
                                    {member.avatarUrl ? (
                                        <img
                                            src={member.avatarUrl}
                                            alt=""
                                            referrerPolicy="no-referrer"
                                            className="size-full object-cover"
                                        />
                                    ) : (
                                        (member.nickname ?? member.name).slice(0, 1)
                                    )}
                                </span>
                                <span className="min-w-0">
                                    <span className="flex flex-wrap items-center gap-2">
                                        <span className="font-semibold">
                                            {member.nickname ? `@${member.nickname}` : copy.noNickname}
                                        </span>
                                        {member.role !== 'member' && (
                                            <Badge tone="horizon">{copy.roles[member.role]}</Badge>
                                        )}
                                        {member.blocked && <Badge tone="car">{copy.blocked}</Badge>}
                                    </span>
                                    <span className="mt-0.5 block truncate text-sm text-night/65">
                                        {member.name} · {member.email}
                                    </span>
                                </span>
                                <span className="col-start-2 text-sm text-night/60 sm:col-start-auto sm:text-right">
                                    {copy.reports(member.reports)} · {copy.joined} {shortDate(member.joinedAt)}
                                </span>
                            </Link>
                        </li>
                    ))}
                </ul>
            )}

            {(page > 1 || hasMore) && (
                <nav className="mt-8 flex justify-between gap-3" aria-label={t.panel.pagesLabel}>
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
        </>
    );
}

Index.layout = (page: ReactNode) => <PanelLayout>{page}</PanelLayout>;

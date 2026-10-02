import { Head, Link } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { Display } from '@/Components/Ui/Typography';
import { t } from '@/i18n/pt-BR';
import { PanelLayout } from '@/Layouts/PanelLayout';

const copy = t.panel.audit;

type Values = Record<string, unknown> | null;

interface Entry {
    id: number;
    action: string;
    actor: string | null;
    subjectType: string;
    subjectId: number;
    before: Values;
    after: Values;
    context: Record<string, unknown>;
    at: string;
}

const show = (value: unknown) => (value === null || value === undefined || value === '' ? '—' : String(value));

/** Only the fields the action changed, as "field: before → after". */
function changes(before: Values, after: Values): string[] {
    const keys = new Set([...Object.keys(before ?? {}), ...Object.keys(after ?? {})]);
    return [...keys]
        .filter((key) => JSON.stringify(before?.[key]) !== JSON.stringify(after?.[key]))
        .map((key) => `${key}: ${show(before?.[key])} → ${show(after?.[key])}`);
}

const when = (iso: string) =>
    new Date(iso).toLocaleString('pt-BR', { dateStyle: 'short', timeStyle: 'short', timeZone: 'America/Sao_Paulo' });

export default function Audit({ items, hasMore, page }: { items: Entry[]; hasMore: boolean; page: number }) {
    return (
        <>
            <Head title={`${copy.title} · ${t.panel.title}`} />
            <Display as="h1" className="text-[clamp(1.8rem,1.2rem+2.4vw,2.8rem)]!">
                {copy.title}
            </Display>
            <p className="mt-3 max-w-[60ch] text-night/70">{copy.lead}</p>

            {items.length === 0 ? (
                <p className="mt-10 font-script text-2xl text-horizon">{copy.empty}</p>
            ) : (
                <ol className="mt-10 divide-y-2 divide-dashed divide-night/12 border-y-2 border-dashed border-night/12">
                    {items.map((entry) => (
                        <li key={entry.id} className="grid gap-2 py-4 sm:grid-cols-[9rem_minmax(0,1fr)] sm:gap-6">
                            <time dateTime={entry.at} className="text-sm text-night/60 tabular-nums">
                                {when(entry.at)}
                            </time>
                            <div className="min-w-0">
                                <p>
                                    <strong className="font-semibold">{entry.actor ?? copy.system}</strong>{' '}
                                    {t.panel.actions[entry.action] ?? entry.action}{' '}
                                    {entry.subjectType === 'sighting' ? (
                                        <Link
                                            href={`/painel/relatos/${entry.subjectId}`}
                                            className="font-semibold text-horizon underline underline-offset-4"
                                        >
                                            {copy.sightingRef(entry.subjectId)}
                                        </Link>
                                    ) : (
                                        `${entry.subjectType} #${entry.subjectId}`
                                    )}
                                </p>
                                <ul className="font-mono mt-1 space-y-0.5 text-[0.8rem] break-words text-night/65">
                                    {changes(entry.before, entry.after).map((line) => (
                                        <li key={line}>{line}</li>
                                    ))}
                                    {Object.entries(entry.context).map(([key, value]) => (
                                        <li key={key}>
                                            {key}: {show(value)}
                                        </li>
                                    ))}
                                </ul>
                            </div>
                        </li>
                    ))}
                </ol>
            )}

            <nav className="mt-8 flex justify-between gap-3" aria-label={t.panel.pagesLabel}>
                {page > 1 ? (
                    <Link
                        href={`/painel/auditoria?pagina=${page - 1}`}
                        className="min-h-11 font-semibold underline underline-offset-4"
                    >
                        {copy.newer}
                    </Link>
                ) : (
                    <span />
                )}
                {hasMore && (
                    <Link
                        href={`/painel/auditoria?pagina=${page + 1}`}
                        className="min-h-11 font-semibold underline underline-offset-4"
                    >
                        {copy.older}
                    </Link>
                )}
            </nav>
        </>
    );
}

Audit.layout = (page: ReactNode) => <PanelLayout>{page}</PanelLayout>;

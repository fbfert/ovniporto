import { Head, Link } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { PanelSection } from '@/Components/Panel/PanelSection';
import { WeeklyChart, type Week } from '@/Components/Panel/WeeklyChart';
import { Display, Eyebrow } from '@/Components/Ui/Typography';
import { longDate, money, t } from '@/i18n/pt-BR';
import { PanelLayout } from '@/Layouts/PanelLayout';

const copy = t.panel.home;

interface Props {
    counts: {
        members: number;
        waitlist: number;
        sightings: number | null;
        orders: number | null;
        revenueMonthCents: number | null;
    };
    goals: { launch: string; deadline: string; items: { key: string; current: number; target: number }[] } | null;
    weekly: Week[];
    needsYou: { kind: string; ref: string; since: string }[];
    sightings: {
        pending: number;
        changesRequested: number;
        approved: number;
        oldestPendingHours: number | null;
        overdue: boolean;
    } | null;
}

const hrefFor = (item: Props['needsYou'][number]) =>
    item.kind === 'sighting' ? `/painel/relatos/${item.ref}` : `/painel/pedidos/${item.ref}`;

export default function Home({ counts, goals, weekly, needsYou, sightings }: Props) {
    const tiles = [
        ['members', String(counts.members)],
        ['sightings', counts.sightings === null ? null : String(counts.sightings)],
        ['orders', counts.orders === null ? null : String(counts.orders)],
        ['revenue', counts.revenueMonthCents === null ? null : money(counts.revenueMonthCents)],
        ['waitlist', String(counts.waitlist)],
    ].filter((tile): tile is [string, string] => tile[1] !== null);

    return (
        <>
            <Head title={t.panel.title} />
            <Eyebrow>{t.panel.eyebrow}</Eyebrow>

            <dl className="mt-4 grid grid-cols-2 gap-x-6 gap-y-5 border-y-2 border-dashed border-night/12 py-6 sm:grid-cols-3 lg:grid-cols-5">
                {tiles.map(([key, value]) => (
                    <div key={key}>
                        <dt className="text-sm text-night/60">{copy.counts[key]}</dt>
                        <dd className="font-display text-[clamp(1.6rem,1.2rem+1.6vw,2.4rem)] leading-tight font-extrabold tabular-nums">
                            {value}
                        </dd>
                    </div>
                ))}
            </dl>

            <section aria-labelledby="precisa" className="mt-10">
                <Display as="h1" id="precisa" className="text-[clamp(1.6rem,1.1rem+2vw,2.4rem)]!">
                    {copy.needsYou}
                </Display>
                {needsYou.length === 0 ? (
                    <p className="mt-3 font-script text-2xl text-horizon">{copy.allClear}</p>
                ) : (
                    <ul className="mt-5 divide-y-2 divide-dashed divide-night/12 border-y-2 border-dashed border-night/12">
                        {needsYou.map((item) => (
                            <li key={`${item.kind}-${item.ref}`}>
                                <Link
                                    href={hrefFor(item)}
                                    className="flex min-h-12 items-center gap-3 py-3 font-semibold hover:underline"
                                >
                                    <span aria-hidden className="size-2.5 shrink-0 rounded-full bg-car" />
                                    {copy.needs[item.kind]?.(item.ref)}
                                </Link>
                            </li>
                        ))}
                    </ul>
                )}
            </section>

            <div className="mt-10 grid gap-8 lg:grid-cols-[minmax(0,1.3fr)_minmax(0,0.7fr)]">
                <PanelSection title={copy.chartTitle}>
                    <WeeklyChart weeks={weekly} />
                </PanelSection>

                <PanelSection title={goals ? copy.goalsTitle(longDate(goals.deadline)) : copy.goalsTitleEmpty}>
                    {!goals || goals.items.length === 0 ? (
                        <p className="text-night/65">{copy.goalsEmpty}</p>
                    ) : (
                        <ul className="space-y-5">
                            {goals.items.map((goal) => {
                                const pct = Math.min(100, Math.round((goal.current / goal.target) * 100));
                                return (
                                    <li key={goal.key}>
                                        <div className="flex items-baseline justify-between gap-3">
                                            <span className="font-semibold">{copy.goalLabels[goal.key]}</span>
                                            <span className="text-sm text-night/65 tabular-nums">
                                                {goal.current} / {goal.target}
                                            </span>
                                        </div>
                                        <div
                                            role="progressbar"
                                            aria-label={copy.goalLabels[goal.key]}
                                            aria-valuenow={pct}
                                            aria-valuemin={0}
                                            aria-valuemax={100}
                                            className="mt-2 h-2.5 overflow-hidden rounded-full bg-night/10"
                                        >
                                            <div
                                                className="h-full rounded-full bg-horizon"
                                                style={{ width: `${pct}%` }}
                                            />
                                        </div>
                                    </li>
                                );
                            })}
                        </ul>
                    )}
                </PanelSection>
            </div>

            {sightings && (
                <Link
                    href="/painel/relatos"
                    className="mt-10 flex flex-wrap items-baseline gap-x-4 gap-y-1 rounded-[22px] bg-night p-6 text-moonlight outline-none focus-visible:ring-4 focus-visible:ring-beam"
                >
                    <span className="font-display text-4xl font-extrabold text-beam">{sightings.pending}</span>
                    <span className="font-semibold">
                        {sightings.pending === 0 ? copy.allClear : copy.pending(sightings.pending)}
                    </span>
                    {sightings.oldestPendingHours !== null && (
                        <span className="text-sm text-moonlight/70">
                            {copy.oldest(t.panel.since(sightings.oldestPendingHours))}
                        </span>
                    )}
                    {sightings.overdue && (
                        <span className="rounded-full bg-car px-2.5 py-1 text-sm font-semibold text-night">
                            {copy.overdue}
                        </span>
                    )}
                    <span className="ml-auto text-sm text-moonlight/70">
                        {copy.changesRequested(sightings.changesRequested)}
                    </span>
                </Link>
            )}

            <Link
                href="/painel/manual"
                className="mt-10 flex min-h-11 flex-wrap items-center gap-x-4 gap-y-1 rounded-[22px] border-2 border-dashed border-horizon/40 p-5 hover:border-horizon"
            >
                <span className="font-display text-sm font-bold tracking-[0.06em] text-horizon uppercase">
                    {t.panel.manual.title}
                </span>
                <span className="text-night/70">{t.panel.manual.homeLead}</span>
            </Link>
        </>
    );
}

Home.layout = (page: ReactNode) => <PanelLayout>{page}</PanelLayout>;

import { Head, Link } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { Display, Eyebrow } from '@/Components/Ui/Typography';
import { t } from '@/i18n/pt-BR';
import { PanelLayout } from '@/Layouts/PanelLayout';

const copy = t.panel.home;

interface Props {
    sightings: {
        pending: number;
        changesRequested: number;
        approved: number;
        oldestPendingHours: number | null;
        overdue: boolean;
    } | null;
}

export default function Home({ sightings }: Props) {
    return (
        <>
            <Head title={t.panel.title} />
            <Eyebrow>{t.panel.eyebrow}</Eyebrow>
            <Display as="h1" className="mt-2 text-[clamp(1.8rem,1.2rem+2.4vw,2.8rem)]!">
                {copy.needsYou}
            </Display>

            {sightings && (
                <section className="mt-10 grid gap-8 lg:grid-cols-[minmax(0,1.2fr)_minmax(0,0.8fr)] lg:items-end">
                    <Link
                        href="/painel/relatos"
                        className="group relative block overflow-hidden rounded-[26px] bg-night p-7 text-moonlight outline-none focus-visible:ring-4 focus-visible:ring-beam sm:p-9"
                    >
                        <span
                            aria-hidden
                            className="absolute -top-16 -right-10 size-56 rounded-full bg-beam/15 blur-3xl"
                        />
                        <span className="relative block font-display text-[clamp(3.5rem,2rem+6vw,6rem)] leading-none font-extrabold text-beam">
                            {sightings.pending}
                        </span>
                        <span className="relative mt-3 block text-lg font-semibold">
                            {sightings.pending === 0 ? copy.allClear : copy.pending(sightings.pending)}
                        </span>
                        {sightings.oldestPendingHours !== null && (
                            <span className="relative mt-2 flex flex-wrap items-center gap-2 text-sm text-moonlight/70">
                                {copy.oldest(t.panel.since(sightings.oldestPendingHours))}
                                {sightings.overdue && (
                                    <span className="rounded-full bg-car px-2.5 py-1 font-semibold text-night">
                                        {copy.overdue}
                                    </span>
                                )}
                            </span>
                        )}
                        <span className="relative mt-7 inline-flex min-h-11 items-center rounded-full bg-beam px-5 font-semibold text-night">
                            {copy.openQueue}
                        </span>
                    </Link>

                    <ul className="divide-y-2 divide-dashed divide-night/12 border-y-2 border-dashed border-night/12 text-[1.05rem]">
                        <li className="py-4">
                            <Link
                                href="/painel/relatos?aba=ajuste"
                                className="font-semibold underline-offset-4 hover:underline"
                            >
                                {copy.changesRequested(sightings.changesRequested)}
                            </Link>
                        </li>
                        <li className="py-4">
                            <Link
                                href="/painel/relatos?aba=aprovados"
                                className="font-semibold underline-offset-4 hover:underline"
                            >
                                {copy.approved(sightings.approved)}
                            </Link>
                        </li>
                    </ul>
                </section>
            )}

            <p className="mt-12 max-w-[52ch] font-script text-2xl text-horizon">{copy.storeSoon}</p>
        </>
    );
}

Home.layout = (page: ReactNode) => <PanelLayout>{page}</PanelLayout>;

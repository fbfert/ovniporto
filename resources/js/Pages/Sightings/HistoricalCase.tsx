import { Link } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { SeoHead } from '@/Components/Layout/SeoHead';
import { HistoricalCaseArt } from '@/Components/Sightings/HistoricalCases';
import { Button } from '@/Components/Ui/Button';
import { Section } from '@/Components/Ui/Section';
import { Display, Eyebrow } from '@/Components/Ui/Typography';
import { t } from '@/i18n/pt-BR';
import { PublicLayout } from '@/Layouts/PublicLayout';
import type { HistoricalCase as Case } from '@/types/historical';

const copy = t.historical;

interface Neighbour {
    slug: string;
    title: string;
}

interface Props {
    case: Case;
    region: string;
    previous: Neighbour | null;
    next: Neighbour | null;
    position: number;
    total: number;
    note: string;
}

function NeighbourLink({ item, label, align }: { item: Neighbour | null; label: string; align: 'left' | 'right' }) {
    if (!item) return <span />;

    return (
        <Link
            href={`/mapa/casos/${item.slug}`}
            className={`group flex min-h-11 flex-col justify-center ${align === 'right' ? 'items-end text-right' : ''}`}
        >
            <span className="text-[0.85rem] text-night/60">{label}</span>
            <span className="font-semibold text-horizon underline-offset-4 group-hover:underline">{item.title}</span>
        </Link>
    );
}

export default function HistoricalCase({ case: item, region, previous, next, position, total }: Props) {
    return (
        <>
            <SeoHead />
            <Section tone="light" innerClassName="pt-32! sm:pt-36!">
                <nav aria-label={t.logbookPage.title} className="text-[0.9rem]">
                    <Link
                        href="/mapa#casos-historicos"
                        className="font-semibold text-horizon underline underline-offset-4"
                    >
                        {t.logbookPage.title}
                    </Link>
                    <span aria-hidden className="mx-2 text-night/40">
                        /
                    </span>
                    <span className="text-night/70">{copy.position(position, total)}</span>
                </nav>

                <div className="mt-8 grid gap-12 lg:grid-cols-[minmax(0,0.9fr)_minmax(0,1.1fr)] lg:items-start">
                    <div>
                        <Eyebrow>{region}</Eyebrow>
                        <Display
                            as="h1"
                            className="mt-3 w-full min-w-0 text-[clamp(1.6rem,0.7rem+2.6vw,3rem)]! text-balance [overflow-wrap:anywhere]"
                        >
                            {item.title}
                        </Display>
                        <dl className="mt-8 grid gap-4 sm:grid-cols-2">
                            <div className="border-t-2 border-horizon pt-3">
                                <dt className="text-[0.85rem] text-night/60">{copy.when}</dt>
                                <dd className="mt-1 font-semibold">
                                    {item.date}
                                    {item.dateNote && (
                                        <span className="block text-[0.9rem] font-normal text-night/65">
                                            {item.dateNote}
                                        </span>
                                    )}
                                </dd>
                            </div>
                            <div className="border-t-2 border-horizon pt-3">
                                <dt className="text-[0.85rem] text-night/60">{copy.place}</dt>
                                <dd className="mt-1 font-semibold">{item.place}</dd>
                            </div>
                        </dl>
                        <p className="mt-8 max-w-[56ch] text-lg leading-relaxed text-night/85">{item.summary}</p>
                    </div>
                    <figure>
                        <HistoricalCaseArt
                            image={item.image}
                            priority
                            sizes="(min-width: 1024px) 44rem, 92vw"
                            className="rounded-[22px]"
                        />
                        <figcaption className="mt-3 text-[0.85rem] text-night/60">{copy.caption}</figcaption>
                    </figure>
                </div>
            </Section>

            <Section tone="dusk" wave>
                <div className="grid gap-12 lg:grid-cols-[minmax(0,1.1fr)_minmax(0,0.9fr)]">
                    <section
                        aria-labelledby="documentacao"
                        className="rounded-[22px] border-2 border-dashed border-horizon/40 bg-horizon/6 p-6 sm:p-8"
                    >
                        <h2
                            id="documentacao"
                            className="font-display text-lg font-extrabold tracking-[0.04em] uppercase"
                        >
                            {copy.documentation}
                        </h2>
                        <p className="mt-4 max-w-[56ch] leading-relaxed text-night/85">{item.documentation}</p>
                        <p className="mt-6 text-[0.9rem] text-night/60">{copy.approximate}</p>
                    </section>
                    <section aria-labelledby="fontes">
                        <h2 id="fontes" className="font-display text-lg font-extrabold tracking-[0.04em] uppercase">
                            {copy.sources}
                        </h2>
                        <ul className="mt-4 divide-y divide-night/10 border-y border-night/10">
                            {item.sources.map((source) => (
                                <li key={source.url}>
                                    <a
                                        href={source.url}
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        className="flex min-h-11 items-center py-3 font-semibold underline decoration-night/30 underline-offset-4 hover:decoration-horizon"
                                    >
                                        {source.label}
                                    </a>
                                </li>
                            ))}
                        </ul>
                    </section>
                </div>

                <div className="mt-16 grid gap-6 border-t border-night/15 pt-8 sm:grid-cols-2">
                    <NeighbourLink item={previous} label={copy.previous} align="left" />
                    <NeighbourLink item={next} label={copy.next} align="right" />
                </div>

                <div className="mt-16 flex flex-col items-start gap-5 sm:flex-row sm:items-center sm:justify-between">
                    <p className="font-script text-[clamp(1.6rem,1.2rem+1.6vw,2.4rem)] leading-tight text-horizon">
                        {copy.reportTitle}
                    </p>
                    <div className="flex flex-wrap items-center gap-4">
                        <Button href="/relatar">{copy.report}</Button>
                        <Button href="/mapa#casos-historicos" variant="ghost">
                            {copy.back}
                        </Button>
                    </div>
                </div>
            </Section>
        </>
    );
}

HistoricalCase.layout = (page: ReactNode) => <PublicLayout>{page}</PublicLayout>;

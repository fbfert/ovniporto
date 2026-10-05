import { router } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { PageCover } from '@/Components/Content/PageCover';
import { SeoHead } from '@/Components/Layout/SeoHead';
import { HistoricalCases } from '@/Components/Sightings/HistoricalCases';
import { LoadMore } from '@/Components/Sightings/LoadMore';
import { SightingPolaroid } from '@/Components/Sightings/SightingPolaroid';
import { SightingsMap, type SightingPin } from '@/Components/Sightings/SightingsMap';
import { Button } from '@/Components/Ui/Button';
import { ListState } from '@/Components/Ui/ListState';
import { ChipGroup } from '@/Components/Ui/Fields';
import { Section } from '@/Components/Ui/Section';
import { Display } from '@/Components/Ui/Typography';
import { useListRequest } from '@/hooks/useListRequest';
import { t } from '@/i18n/pt-BR';
import { PublicLayout } from '@/Layouts/PublicLayout';
import type { SightingCard } from '@/types';
import type { HistoricalSection } from '@/types/historical';

const copy = t.logbookPage;
const ALL = 'all';
const TYPE_OPTIONS = [
    { value: ALL, label: copy.allTypes },
    ...Object.entries(t.logbook.types).map(([value, label]) => ({ value, label })),
];
const ROTATIONS = [-3, 2, -1.5, 3, -2, 1.5];

interface Props {
    filters: { periodo: string; tipo: string | null };
    total: number;
    pins: SightingPin[];
    cards: SightingCard[];
    page: number;
    hasMore: boolean;
    historical: HistoricalSection;
}

export default function Logbook({ filters, total, pins, cards, page, hasMore, historical }: Props) {
    const list = useListRequest();
    const more = useListRequest();

    /** Filters live in the URL; a new filter resets the merged list of polaroids. */
    const applyFilters = (periodo: string, tipo: string | null) =>
        list.run((callbacks) =>
            router.get(
                '/mapa',
                { periodo, ...(tipo ? { tipo } : {}) },
                {
                    preserveState: true,
                    preserveScroll: true,
                    replace: true,
                    reset: ['cards'],
                    only: ['filters', 'total', 'pins', 'cards', 'page', 'hasMore'],
                    ...callbacks,
                },
            ),
        );

    const loadMore = (nextPage: number, done: () => void) =>
        more.run((callbacks) =>
            router.reload({
                data: { pagina: nextPage },
                only: ['cards', 'page', 'hasMore'],
                preserveUrl: true,
                ...callbacks,
                onFinish: done,
            }),
        );

    return (
        <>
            <SeoHead />
            <PageCover eyebrow={copy.eyebrow} title={copy.title}>
                <p className="mt-6 font-script text-[clamp(1.5rem,1.2rem+1vw,2rem)] text-beam-glow" aria-live="polite">
                    {copy.count(total)}
                </p>
            </PageCover>

            <section data-tone="dark" className="relative bg-night text-moonlight" aria-label={copy.mapLabel}>
                <div className="sticky top-20 z-[600] mx-auto flex max-w-[84rem] flex-col gap-3 px-5 py-4 sm:px-8 lg:flex-row lg:items-center lg:justify-between">
                    <ChipGroup
                        tone="dark"
                        label={copy.periodLabel}
                        hideLabel
                        options={copy.periods}
                        value={filters.periodo}
                        onChange={(periodo) => applyFilters(periodo, filters.tipo)}
                        className="rounded-[26px] bg-night/80 p-1.5 backdrop-blur-[8px]"
                    />
                    <ChipGroup
                        tone="dark"
                        label={copy.typeLabel}
                        hideLabel
                        options={TYPE_OPTIONS}
                        value={filters.tipo ?? ALL}
                        onChange={(tipo) => applyFilters(filters.periodo, tipo === ALL ? null : tipo)}
                        className="rounded-[26px] bg-night/80 p-1.5 backdrop-blur-[8px]"
                    />
                </div>
                <SightingsMap pins={pins} historical={historical.pins} className="h-[70svh] min-h-[420px] w-full" />
                <ul className="mx-auto flex max-w-[84rem] flex-wrap gap-x-6 gap-y-2 px-5 py-4 text-[0.85rem] text-moonlight/75 sm:px-8">
                    <li className="flex items-center gap-2">
                        <span aria-hidden className="sighting-dot scale-75" />
                        {copy.legendReport}
                    </li>
                    <li className="flex items-center gap-2">
                        <span aria-hidden className="historical-dot scale-90" />
                        {copy.legendHistorical}
                    </li>
                </ul>
                {pins.length === 0 && (
                    <p className="pointer-events-none absolute inset-x-0 bottom-10 z-[600] text-center font-script text-2xl text-beam-glow">
                        {copy.mapEmpty}
                    </p>
                )}
            </section>

            <Section tone="dark" pattern="stars" labelledBy="relatos">
                <Display as="h2" id="relatos" className="text-[clamp(1.6rem,1rem+2.4vw,2.6rem)]!">
                    {copy.listTitle}
                </Display>
                <ListState
                    status={list.status}
                    tone="dark"
                    onRetry={list.retry}
                    isEmpty={cards.length === 0}
                    placeholderClassName="aspect-[4/5] rounded-[6px]"
                    className="mt-12 grid grid-cols-2 gap-x-5 gap-y-10 sm:grid-cols-3 sm:gap-x-8 lg:grid-cols-4"
                    empty={
                        <div className="mt-10 rounded-[22px] border-2 border-dashed border-moonlight/20 p-10 text-center">
                            <p className="font-script text-2xl text-beam-glow">{copy.empty}</p>
                            <div className="mt-5">
                                <Button href="/relatar">{copy.reportLong}</Button>
                            </div>
                        </div>
                    }
                >
                    <ul className="mt-12 grid grid-cols-2 gap-x-5 gap-y-10 sm:grid-cols-3 sm:gap-x-8 lg:grid-cols-4">
                        {cards.map((sighting, i) => (
                            <li key={sighting.id}>
                                <SightingPolaroid
                                    sighting={sighting}
                                    rotate={ROTATIONS[i % ROTATIONS.length]}
                                    tape={i % 2 === 0 ? 'top' : 'corner'}
                                />
                            </li>
                        ))}
                    </ul>
                </ListState>
                {more.status === 'error' ? (
                    <ListState status="error" tone="dark" onRetry={more.retry} isEmpty={false} empty={null}>
                        {null}
                    </ListState>
                ) : (
                    <LoadMore page={page} hasMore={hasMore} onLoad={loadMore} />
                )}
            </Section>

            <HistoricalCases section={historical} />

            <div className="fixed right-4 bottom-[max(1rem,env(safe-area-inset-bottom))] z-40 lg:hidden">
                <Button href="/relatar" size="lg" className="h-14 shadow-beam">
                    {copy.report}
                </Button>
            </div>
        </>
    );
}

Logbook.layout = (page: ReactNode) => <PublicLayout>{page}</PublicLayout>;

import { router, usePage } from '@inertiajs/react';
import { useEffect, useMemo, useRef, useState, type ReactNode } from 'react';
import { MailIcon } from '@/Components/Icons';
import { SeoHead } from '@/Components/Layout/SeoHead';
import { LazyMap, ovniportoMarker, type MapMarker } from '@/Components/Map/LazyMap';
import { PartnerTile, type PartnerListing } from '@/Components/Region/PartnerTile';
import { Button } from '@/Components/Ui/Button';
import { ChipGroup, TextField } from '@/Components/Ui/Fields';
import { ListState } from '@/Components/Ui/ListState';
import { NightSkyArt, Polaroid } from '@/Components/Ui/Polaroid';
import { Reveal, RevealItem } from '@/Components/Ui/Reveal';
import { Section } from '@/Components/Ui/Section';
import { Display, Eyebrow } from '@/Components/Ui/Typography';
import { useListRequest } from '@/hooks/useListRequest';
import { t } from '@/i18n/pt-BR';
import { PublicLayout } from '@/Layouts/PublicLayout';
import type { SharedProps } from '@/types';

const copy = t.regionPage;
const ALL = 'all';
const TYPE_OPTIONS = [
    { value: ALL, label: copy.all },
    ...Object.entries(copy.filters).map(([value, label]) => ({ value, label })),
];
const VIEW_OPTIONS = [
    { value: 'list', label: copy.list },
    { value: 'map', label: copy.map },
];

interface Props {
    partners: PartnerListing[];
    total: number;
    filters: { tipo: string | null; q: string | null };
}

function EmptyRegion({ email }: { email: string }) {
    const mailto = `mailto:${email}?subject=${encodeURIComponent(copy.joinSubject)}`;
    return (
        <div className="grid items-center gap-12 md:grid-cols-[minmax(0,0.8fr)_minmax(0,1.2fr)]">
            <div className="mx-auto w-full max-w-xs md:mx-0">
                <Polaroid rotate={-3} tape="top" art={<NightSkyArt label="Serra" seed={21} />} caption={copy.empty} />
            </div>
            <div>
                <p className="max-w-[34ch] font-script text-[clamp(1.6rem,1.2rem+1.4vw,2.2rem)] leading-tight text-horizon">
                    {copy.empty}
                </p>
                <div className="mt-8">
                    <Button href={mailto} external iconLeft={<MailIcon size="1.1rem" />}>
                        {t.region.join}
                    </Button>
                </div>
            </div>
        </div>
    );
}

export default function RegionIndex({ partners, total, filters }: Props) {
    const { community } = usePage<SharedProps>().props;
    const [view, setView] = useState('list');
    const [search, setSearch] = useState(filters.q ?? '');
    const list = useListRequest();

    /** Partial reload: only the list and the filters travel; the URL keeps the state for sharing. */
    const applyFilters = (tipo: string | null, q: string) => {
        const query: Record<string, string> = {};
        if (tipo) query.tipo = tipo;
        if (q.trim()) query.q = q.trim();
        list.run((callbacks) =>
            router.get('/regiao', query, {
                only: ['partners', 'filters'],
                preserveState: true,
                preserveScroll: true,
                replace: true,
                ...callbacks,
            }),
        );
    };
    const first = useRef(true);

    // Debounced search: the request goes out 300ms after the last keystroke.
    useEffect(() => {
        if (first.current) {
            first.current = false;
            return;
        }
        const timer = setTimeout(() => applyFilters(filters.tipo, search), 300);
        return () => clearTimeout(timer);
        // eslint-disable-next-line react-hooks/exhaustive-deps -- only the text should trigger this
    }, [search]);

    const markers = useMemo<MapMarker[]>(
        () => [
            ovniportoMarker(),
            ...partners
                .filter((p) => p.lat !== null && p.lng !== null)
                .map((p) => ({ id: p.slug, lat: p.lat!, lng: p.lng!, label: p.name, href: `/regiao/${p.slug}` })),
        ],
        [partners],
    );

    return (
        <>
            <SeoHead />

            <Section tone="light" innerClassName="pt-36! sm:pt-40!">
                <Eyebrow>{copy.eyebrow}</Eyebrow>
                <Display as="h1" className="mt-3 text-[clamp(2rem,0.6rem+6vw,5.5rem)]! text-balance">
                    {copy.title}
                </Display>
                <p className="mt-5 max-w-[46ch] text-lg leading-relaxed text-night/80">{copy.lead}</p>

                {total === 0 ? (
                    <div className="mt-16">
                        <EmptyRegion email={community.email} />
                    </div>
                ) : (
                    <>
                        <div className="mt-12 flex flex-col gap-6 border-y-2 border-dashed border-night/15 py-6 lg:flex-row lg:items-end lg:justify-between">
                            <ChipGroup
                                label={copy.filterLabel}
                                hideLabel
                                options={TYPE_OPTIONS}
                                value={filters.tipo ?? ALL}
                                onChange={(value) => applyFilters(value === ALL ? null : value, search)}
                            />
                            <div className="flex flex-col gap-4 sm:flex-row sm:items-end">
                                <TextField
                                    type="search"
                                    label={copy.searchLabel}
                                    hideLabel
                                    placeholder={copy.searchPlaceholder}
                                    value={search}
                                    onChange={(event) => setSearch(event.target.value)}
                                    className="sm:w-72"
                                />
                                <ChipGroup
                                    label={copy.viewLabel}
                                    hideLabel
                                    options={VIEW_OPTIONS}
                                    value={view}
                                    onChange={setView}
                                />
                            </div>
                        </div>

                        <p className="mt-6 text-sm font-semibold text-night/60" aria-live="polite">
                            {copy.count(partners.length)}
                        </p>

                        <ListState
                            status={list.status}
                            onRetry={list.retry}
                            isEmpty={partners.length === 0}
                            placeholders={3}
                            placeholderClassName="aspect-[4/3] rounded-[22px]"
                            className="mt-8 grid gap-x-6 gap-y-10 sm:grid-cols-2 lg:grid-cols-3"
                            empty={
                                <div className="mt-8 rounded-[22px] border-2 border-dashed border-night/20 p-10 text-center">
                                    <p className="font-script text-2xl text-horizon">{copy.noResults}</p>
                                    <div className="mt-5">
                                        <Button
                                            variant="secondary"
                                            onClick={() => {
                                                setSearch('');
                                                applyFilters(null, '');
                                            }}
                                        >
                                            {copy.clear}
                                        </Button>
                                    </div>
                                </div>
                            }
                        >
                            {view === 'map' ? (
                                <LazyMap
                                    markers={markers}
                                    label={copy.mapLabel}
                                    zoom={11}
                                    className="mt-8 aspect-[4/5] w-full sm:aspect-[16/9]"
                                />
                            ) : (
                                <Reveal
                                    stagger
                                    as="ul"
                                    className="mt-8 grid gap-x-6 gap-y-10 sm:grid-cols-2 lg:grid-cols-3"
                                >
                                    {partners.map((partner) => (
                                        <RevealItem as="li" key={partner.slug}>
                                            <PartnerTile partner={partner} />
                                        </RevealItem>
                                    ))}
                                </Reveal>
                            )}
                        </ListState>
                    </>
                )}
            </Section>
        </>
    );
}

RegionIndex.layout = (page: ReactNode) => <PublicLayout>{page}</PublicLayout>;

import { usePage } from '@inertiajs/react';
import { useState, type ReactNode } from 'react';
import { SeoHead } from '@/Components/Layout/SeoHead';
import { LazyMap } from '@/Components/Map/LazyMap';
import { Compass } from '@/Components/Report/Compass';
import { SharePostcard } from '@/Components/Sightings/SharePostcard';
import { SightingPolaroid } from '@/Components/Sightings/SightingPolaroid';
import { TowerStamp } from '@/Components/Sightings/TowerStamp';
import { Button } from '@/Components/Ui/Button';
import { Modal } from '@/Components/Ui/Modal';
import { ContentImage, type PhotoSources } from '@/Components/Ui/Picture';
import { NightSkyArt } from '@/Components/Ui/Polaroid';
import { Section } from '@/Components/Ui/Section';
import { Badge, Display, Eyebrow } from '@/Components/Ui/Typography';
import { longDate, t } from '@/i18n/pt-BR';
import { PublicLayout } from '@/Layouts/PublicLayout';
import type { SharedProps, SightingCard } from '@/types';

const copy = t.sightingPage;

interface Sighting {
    id: number;
    type: 'light' | 'object' | 'trail' | 'other';
    description: string;
    observedDate: string;
    timeRange: string | null;
    exactTime: string | null;
    lat: number;
    lng: number;
    place: string | null;
    gaze: string | null;
    nickname: string;
    status: string;
    publishedAt: string | null;
    photos: { thumb: string | null; full: string | null; sources: PhotoSources | null }[];
}

function Fact({ label, children }: { label: string; children: ReactNode }) {
    return (
        <div className="border-b-2 border-dashed border-moonlight/12 py-3">
            <dt className="text-[0.7rem] font-semibold tracking-[0.12em] text-moonlight/55 uppercase">{label}</dt>
            <dd className="mt-1 text-[1.05rem]">{children}</dd>
        </div>
    );
}

function Gallery({ sighting }: { sighting: Sighting }) {
    const [open, setOpen] = useState<number | null>(null);
    const type = t.logbook.types[sighting.type];
    if (sighting.photos.length === 0) {
        return (
            <figure className="relative aspect-[4/5] overflow-hidden rounded-[22px] bg-night-blue">
                <NightSkyArt label={type} seed={sighting.id} />
                <figcaption className="absolute inset-x-0 bottom-0 p-5 text-center font-script text-xl text-moonlight/85">
                    {copy.noPhotos}
                </figcaption>
            </figure>
        );
    }
    const current = open === null ? null : sighting.photos[open];
    return (
        <>
            <ul aria-label={copy.photosLabel} className="grid grid-cols-2 gap-3">
                {sighting.photos.map((photo, i) => (
                    <li key={i} className={i === 0 ? 'col-span-2' : ''}>
                        <button
                            type="button"
                            onClick={() => setOpen(i)}
                            className="block w-full overflow-hidden rounded-[18px] bg-night-blue"
                        >
                            <ContentImage
                                src={(i === 0 ? photo.full : photo.thumb) ?? ''}
                                alt={`${type} vista por ${sighting.nickname}, foto ${i + 1}`}
                                sources={photo.sources}
                                sizes={i === 0 ? '(min-width: 1024px) 55vw, 100vw' : '(min-width: 1024px) 25vw, 50vw'}
                                priority={i === 0}
                                imgClassName={`w-full object-cover ${i === 0 ? 'aspect-[4/3]' : 'aspect-square'}`}
                            />
                        </button>
                    </li>
                ))}
            </ul>
            <Modal
                open={current != null}
                onClose={() => setOpen(null)}
                title={open === null ? '' : copy.photoOf(open + 1, sighting.photos.length)}
                className="max-w-3xl!"
            >
                {current?.full && (
                    <ContentImage
                        src={current.full}
                        alt={`${type} vista por ${sighting.nickname}`}
                        sources={current.sources}
                        sizes="(min-width: 768px) 48rem, 100vw"
                        className="overflow-hidden rounded-[14px]"
                        imgClassName="w-full rounded-[14px]"
                    />
                )}
            </Modal>
        </>
    );
}

export default function Show({
    sighting,
    nearby,
    ownPending,
}: {
    sighting: Sighting;
    nearby: SightingCard[];
    ownPending: boolean;
}) {
    const { appUrl } = usePage<SharedProps>().props;
    const type = t.logbook.types[sighting.type];
    const time =
        sighting.exactTime ?? t.report.when.ranges.find((range) => range.value === sighting.timeRange)?.label ?? '';
    const url = `${appUrl}/relatos/${sighting.id}`;

    return (
        <>
            <SeoHead />
            <Section tone="dark" pattern="stars" innerClassName="pt-32! sm:pt-36!">
                {ownPending && (
                    <p role="status" className="mb-10 rounded-2xl bg-car px-5 py-3 font-semibold text-night">
                        {copy.pending}
                    </p>
                )}
                <div className="grid gap-12 lg:grid-cols-[minmax(0,1.1fr)_minmax(0,0.9fr)] lg:gap-16">
                    {/* Photo stays in view while the longer story card scrolls past it. */}
                    <div className="self-start lg:sticky lg:top-28">
                        <Gallery sighting={sighting} />
                    </div>

                    <article className="relative self-start rounded-[26px] bg-night-blue p-6 ring-1 ring-moonlight/10 sm:p-8">
                        {sighting.status === 'approved' && sighting.publishedAt && (
                            <TowerStamp
                                date={sighting.publishedAt}
                                className="absolute -top-10 -right-1 sm:-right-6 lg:-right-8"
                            />
                        )}
                        <Eyebrow tone="dark">
                            {copy.by}: {sighting.nickname}
                        </Eyebrow>
                        <Display as="h1" className="mt-3 text-[clamp(1.7rem,1rem+3vw,3rem)]!">
                            {type}
                        </Display>
                        <dl className="mt-6">
                            <Fact label={copy.when}>
                                {longDate(sighting.observedDate)}
                                {time && ` · ${time}`}
                            </Fact>
                            {sighting.place && <Fact label={copy.where}>{sighting.place}</Fact>}
                            {sighting.gaze && (
                                <Fact label={copy.gaze}>
                                    <span className="flex items-center gap-4">
                                        <Compass direction={sighting.gaze} />
                                        <Badge tone="beam">{sighting.gaze}</Badge>
                                    </span>
                                </Fact>
                            )}
                        </dl>
                        <p className="prose-ovni mt-6 text-moonlight/90">{sighting.description}</p>
                        <LazyMap
                            markers={[{ id: String(sighting.id), lat: sighting.lat, lng: sighting.lng, label: type }]}
                            label={copy.mapLabel}
                            zoom={11}
                            className="mt-8 aspect-[4/3] w-full"
                        />
                    </article>
                </div>
            </Section>

            {!ownPending && (
                <Section tone="dark" wave labelledBy="perto">
                    <div className="grid gap-12 lg:grid-cols-[minmax(0,1.4fr)_minmax(0,0.6fr)] lg:items-start">
                        <div>
                            <Display as="h2" id="perto" className="text-[clamp(1.4rem,1rem+2vw,2.2rem)]!">
                                {copy.nearbyTitle}
                            </Display>
                            {nearby.length === 0 ? (
                                <p className="mt-6 font-script text-2xl text-beam-glow">{copy.nearbyEmpty}</p>
                            ) : (
                                <ul className="mt-10 grid grid-cols-2 gap-6 sm:grid-cols-3">
                                    {nearby.map((card, i) => (
                                        <li key={card.id}>
                                            <SightingPolaroid sighting={card} rotate={[-2, 2, -1][i] ?? 0} />
                                        </li>
                                    ))}
                                </ul>
                            )}
                            <div className="mt-10">
                                <Button href="/mapa" variant="secondary" tone="dark">
                                    {copy.backToMap}
                                </Button>
                            </div>
                        </div>
                        <SharePostcard url={url} />
                    </div>
                </Section>
            )}
        </>
    );
}

Show.layout = (page: ReactNode) => <PublicLayout>{page}</PublicLayout>;

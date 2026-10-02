import type { ReactNode } from 'react';
import { WaitlistForm } from '@/Components/Home/WaitlistForm';
import { BeamIcon, CompassIcon, StampIcon } from '@/Components/Icons';
import { SeoHead } from '@/Components/Layout/SeoHead';
import { OVNIPORTO_COORDS, PlaceMap } from '@/Components/Place/PlaceMap';
import { SitePhotos, type SitePhoto } from '@/Components/Place/SitePhotos';
import { SpaceConcept } from '@/Components/Place/SpaceConcept';
import { Button } from '@/Components/Ui/Button';
import { ConceptImage } from '@/Components/Ui/ConceptImage';
import { Picture } from '@/Components/Ui/Picture';
import { Reveal, RevealItem } from '@/Components/Ui/Reveal';
import { Section } from '@/Components/Ui/Section';
import { Badge, Display, Eyebrow } from '@/Components/Ui/Typography';
import { t } from '@/i18n/pt-BR';
import { PublicLayout } from '@/Layouts/PublicLayout';
import type { PlaceSpace } from '@/types';

const copy = t.placePage;
const PHASES = [1, 2, 3, 4] as const;
const SKY_ICONS = [<BeamIcon key="light" />, <CompassIcon key="paths" />, <StampIcon key="qr" />];
const GOOGLE_MAPS = `https://www.google.com/maps/search/?api=1&query=${OVNIPORTO_COORDS.join(',')}`;

/** Cover over the overview concept art: the honest version is "this is how it will look". */
function PlaceCover() {
    return (
        <section
            data-tone="dark"
            className="relative isolate flex min-h-[78svh] items-end overflow-hidden bg-night text-moonlight"
        >
            <Picture
                slug="overview"
                alt=""
                priority
                className="absolute inset-0 -z-10"
                imgClassName="h-full w-full object-cover object-[55%_50%]"
            />
            <div className="absolute inset-0 -z-10 bg-[linear-gradient(to_bottom,rgb(6_17_33/0.55),rgb(6_17_33/0.25)_40%,var(--color-night))]" />
            <Badge tone="horizon" className="absolute top-[5.5rem] right-4 sm:right-6">
                {t.concept.badge}
            </Badge>
            <div className="mx-auto w-full max-w-[84rem] px-5 pt-40 pb-16 sm:px-8 sm:pb-20">
                <Eyebrow tone="dark">{copy.eyebrow}</Eyebrow>
                <Display as="h1" className="mt-3 text-[clamp(2.4rem,1rem+7vw,6.5rem)]!">
                    {copy.title}
                </Display>
                <Badge tone="car" className="mt-5">
                    {copy.goal}
                </Badge>
                <p className="mt-6 max-w-[46ch] text-lg leading-relaxed text-moonlight/85">{copy.lead}</p>
            </div>
        </section>
    );
}

function SpaceCard({ space, number }: { space: PlaceSpace; number: number }) {
    return (
        <article className="flex h-full flex-col overflow-hidden rounded-[22px] bg-moonlight ring-1 ring-night/12">
            <SpaceConcept
                space={space}
                sizes="(min-width: 768px) 22rem, 90vw"
                className="aspect-[16/10] bg-night"
                pendingClassName="aspect-[16/10]"
            />
            <div className="flex flex-1 flex-col p-6">
                <div className="flex items-baseline gap-3">
                    <Display as="span" outlined className="text-3xl leading-none text-horizon">
                        {String(number).padStart(2, '0')}
                    </Display>
                    <h4 className="font-display text-[1.05rem] leading-tight font-bold tracking-[0.03em] uppercase">
                        {space.name}
                    </h4>
                </div>
                <p className="mt-3 leading-relaxed text-night/75">{space.description ?? space.role}</p>
                <p className="mt-auto pt-5">
                    <Badge tone={space.status === 'building' ? 'car' : 'neutral'}>{t.place.status[space.status]}</Badge>
                </p>
            </div>
        </article>
    );
}

function PhaseTimeline({ spaces }: { spaces: PlaceSpace[] }) {
    return (
        <ol className="relative mt-14 space-y-14 border-l-2 border-dashed border-night/15 pl-6 sm:pl-10">
            {PHASES.map((phase) => {
                const inPhase = spaces.filter((space) => space.phase === phase);
                if (inPhase.length === 0) return null;
                const first = phase === 1;
                return (
                    <li key={phase} className="relative">
                        <span
                            aria-hidden
                            className={`absolute top-2 -left-[calc(1.5rem+7px)] size-3 rounded-full sm:-left-[calc(2.5rem+7px)] ${first ? 'bg-beam shadow-[0_0_0_6px_rgb(84_201_51/0.25)]' : 'bg-horizon'}`}
                        />
                        <div className="flex flex-wrap items-center gap-3">
                            <h3 className="font-display text-2xl font-extrabold tracking-[0.04em] uppercase">
                                {copy.phaseTitle(phase)}
                            </h3>
                            {first && <Badge tone="beam">{copy.phaseFirst}</Badge>}
                        </div>
                        <Reveal
                            stagger
                            as="ul"
                            className={`mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3 ${first ? '-mx-3 rounded-[28px] bg-beam/10 p-3 ring-1 ring-beam/40 sm:-mx-4 sm:p-4' : ''}`}
                        >
                            {inPhase.map((space) => (
                                <RevealItem as="li" key={space.slug}>
                                    {/* spaces arrive ordered by phase, so the list position is the running number */}
                                    <SpaceCard space={space} number={spaces.indexOf(space) + 1} />
                                </RevealItem>
                            ))}
                        </Reveal>
                    </li>
                );
            })}
        </ol>
    );
}

export default function Place({
    spaces,
    photos,
    map3d,
}: {
    spaces: PlaceSpace[];
    photos: SitePhoto[];
    map3d: string | null;
}) {
    return (
        <>
            <SeoHead title={copy.title} description={copy.description} image="/concept/overview.jpg" />
            <PlaceCover />

            <Section tone="light" labelledBy="onde">
                <div className="grid gap-10 lg:grid-cols-[minmax(0,0.8fr)_minmax(0,1.2fr)] lg:items-center lg:gap-16">
                    <div>
                        <Eyebrow>{copy.whereEyebrow}</Eyebrow>
                        <Display as="h2" id="onde" className="mt-3">
                            {copy.whereTitle}
                        </Display>
                        <p className="mt-5 max-w-[40ch] text-lg leading-relaxed text-night/80">{copy.whereLead}</p>
                        <div className="mt-8">
                            <Button href={GOOGLE_MAPS} external variant="secondary">
                                {copy.openInGoogle}
                            </Button>
                        </div>
                    </div>
                    <PlaceMap className="aspect-[4/3] w-full lg:aspect-[16/10]" />
                </div>
            </Section>

            <Section tone="dusk" wave labelledBy="hoje-e-amanha">
                <Display as="h2" id="hoje-e-amanha">
                    {copy.todayTitle}
                </Display>
                <div className="mt-10 grid gap-8 md:grid-cols-2">
                    <figure>
                        <SitePhotos photos={photos} className="min-h-64 md:aspect-[16/10]" />
                        <figcaption className="mt-3 px-1 text-sm font-semibold">{copy.today}</figcaption>
                    </figure>
                    <figure>
                        <ConceptImage
                            slug="overview"
                            sizes="(min-width: 768px) 50vw, 100vw"
                            className="aspect-[16/10] rounded-[22px] bg-night"
                            badgeClassName="top-4 right-4"
                        />
                        <figcaption className="mt-3 px-1 text-sm font-semibold">{copy.future}</figcaption>
                    </figure>
                </div>
                <section
                    aria-labelledby="mapa-3d"
                    className="mt-12 rounded-[22px] border-2 border-dashed border-night/25 p-8 text-center"
                >
                    <h3 id="mapa-3d" className="font-display text-lg font-bold tracking-[0.04em] uppercase">
                        {copy.mapTitle}
                    </h3>
                    {map3d ? (
                        <iframe
                            src={map3d}
                            title={copy.mapTitle}
                            loading="lazy"
                            allow="fullscreen; xr-spatial-tracking"
                            referrerPolicy="strict-origin-when-cross-origin"
                            sandbox="allow-scripts allow-same-origin allow-popups"
                            className="mt-6 aspect-[16/10] w-full rounded-[18px] bg-night"
                        />
                    ) : (
                        <p className="mt-2 font-script text-2xl text-horizon">{copy.map3dPending}</p>
                    )}
                </section>
            </Section>

            <Section tone="light" wave labelledBy="fases">
                <Eyebrow>{copy.phasesEyebrow}</Eyebrow>
                <Display as="h2" id="fases" className="mt-3">
                    {copy.phasesTitle}
                </Display>
                <PhaseTimeline spaces={spaces} />
            </Section>

            <Section tone="dark" pattern="stars" wave labelledBy="ceu-escuro">
                <Eyebrow tone="dark">{copy.skyEyebrow}</Eyebrow>
                <Display as="h2" id="ceu-escuro" className="mt-3">
                    {copy.skyTitle}
                </Display>
                <ol className="mt-12 grid gap-10 md:grid-cols-3 md:gap-8">
                    {copy.skyRules.map((rule, i) => (
                        <li key={rule.title} className="border-t-2 border-dashed border-moonlight/20 pt-6">
                            <div className="flex items-center gap-3 text-beam-glow">
                                <Display as="span" outlined className="text-4xl leading-none">
                                    {String(i + 1).padStart(2, '0')}
                                </Display>
                                {SKY_ICONS[i]}
                            </div>
                            <h3 className="mt-5 font-display text-lg font-bold tracking-[0.03em] uppercase">
                                {rule.title}
                            </h3>
                            <p className="mt-2 max-w-[38ch] leading-relaxed text-moonlight/75">{rule.body}</p>
                        </li>
                    ))}
                </ol>

                <div className="mt-20 rounded-[26px] bg-night-blue/70 p-6 ring-1 ring-moonlight/10 sm:p-10 lg:grid lg:grid-cols-2 lg:items-center lg:gap-12">
                    <div>
                        <h2 className="font-display text-2xl font-bold tracking-[0.03em] uppercase">{copy.closing}</h2>
                        <p className="mt-3 mb-6 max-w-[46ch] text-moonlight/75 lg:mb-0">{copy.closingLead}</p>
                    </div>
                    <WaitlistForm source="o-lugar" />
                </div>
            </Section>
        </>
    );
}

Place.layout = (page: ReactNode) => <PublicLayout>{page}</PublicLayout>;

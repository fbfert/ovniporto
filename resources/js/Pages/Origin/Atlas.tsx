import { Link } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { PageCover } from '@/Components/Content/PageCover';
import { SeoHead } from '@/Components/Layout/SeoHead';
import { LazyMap, OVNIPORTO_COORDS, type MapMarker } from '@/Components/Map/LazyMap';
import { CreditedImage } from '@/Components/Origin/CreditedImage';
import { ConfidenceSeal } from '@/Components/Origin/SourceBadge';
import { Timeline } from '@/Components/Origin/Timeline';
import { Accordion } from '@/Components/Ui/Accordion';
import { Badge, Display, Eyebrow } from '@/Components/Ui/Typography';
import { Reveal } from '@/Components/Ui/Reveal';
import { Section } from '@/Components/Ui/Section';
import { t } from '@/i18n/pt-BR';
import { PublicLayout } from '@/Layouts/PublicLayout';
import type { AtlasCandidate, AtlasCaseSummary, AtlasSource, GradeMeaning, ImageCredits } from '@/types/origin';

const copy = t.origin.atlasPage;
const LAGES = 'lages';

interface Props {
    title: string;
    subtitle: string;
    summary: string;
    revision: string | null;
    scope: string[];
    method: string[];
    grades: GradeMeaning[];
    cases: AtlasCaseSummary[];
    timeline: { date: string; place: string; event: string }[];
    timelineNote: string;
    crossQuestions: string[];
    candidatesNote: string;
    candidates: AtlasCandidate[];
    sources: AtlasSource[];
    limitations: string[];
    images: ImageCredits;
}

/** Every case with a coordinate becomes a dot; Lages, still a project, is our own saucer on the map. */
function markers(cases: AtlasCaseSummary[]): MapMarker[] {
    return cases.flatMap((c): MapMarker[] => {
        const href = `/origem/atlas/${c.slug}`;
        if (c.slug === LAGES) {
            return [
                {
                    id: c.slug,
                    lat: OVNIPORTO_COORDS[0],
                    lng: OVNIPORTO_COORDS[1],
                    label: `${c.name} · ${c.country} (${copy.planned})`,
                    href,
                    highlight: true,
                },
            ];
        }
        if (!c.coordinates) return [];
        const label = `${c.name} · ${c.country}${c.coordinates.approximate ? ` (${copy.approximate})` : ''}`;
        return [{ id: c.slug, lat: c.coordinates.lat, lng: c.coordinates.lng, label, href }];
    });
}

/** A case as a passport stamp: number, confidence seal, name, country and what kind of place it is. */
function Stamp({ item }: { item: AtlasCaseSummary }) {
    return (
        <Link
            href={`/origem/atlas/${item.slug}`}
            className="group/stamp flex h-full flex-col rounded-[22px] border-2 border-dashed border-night/20 bg-moonlight p-5 transition-colors duration-200 hover:border-horizon"
        >
            <div className="flex items-start justify-between gap-4">
                <span className="font-display text-[0.72rem] font-bold tracking-[0.08em] text-horizon uppercase">
                    {copy.caseNumber(item.number)}
                </span>
                <ConfidenceSeal seal={item.seal} size="sm" />
            </div>
            <span className="mt-3 font-display text-[1.05rem] leading-tight font-extrabold tracking-[0.03em] uppercase group-hover/stamp:underline">
                {item.name}
            </span>
            <span className="mt-1 font-script text-xl text-horizon">{item.country}</span>
            {item.category && <span className="mt-3 text-[0.9rem] leading-snug text-night/70">{item.category}</span>}
            {item.slug === LAGES && (
                <Badge tone="car" className="mt-4 self-start">
                    {copy.planned}
                </Badge>
            )}
        </Link>
    );
}

function SourceRow({ source }: { source: AtlasSource }) {
    return (
        <li className="flex flex-col gap-1.5 py-4 sm:flex-row sm:items-baseline sm:gap-5">
            <span className="flex-1">
                {source.url ? (
                    <a
                        href={source.url}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="font-semibold underline decoration-night/30 underline-offset-4 hover:decoration-horizon"
                    >
                        {source.title}
                    </a>
                ) : (
                    <span className="font-semibold">{source.title}</span>
                )}
                <span className="block text-[0.9rem] text-night/65">
                    {[source.author, source.publisher, source.date].filter(Boolean).join(' · ')}
                </span>
                <span className="mt-1 block text-[0.9rem] leading-relaxed text-night/75">{source.note}</span>
            </span>
            <span className="flex shrink-0 flex-wrap items-center gap-2 text-[0.8rem] text-night/60">
                {source.grade && <Badge tone="horizon">{`${t.origin.confidence} ${source.grade}`}</Badge>}
                {source.accessed && copy.accessed(source.accessed)}
            </span>
        </li>
    );
}

function Candidate({ item }: { item: AtlasCandidate }) {
    return (
        <article className="flex h-full flex-col rounded-[22px] bg-night-blue p-6 text-moonlight">
            <h3 className="font-display text-base leading-tight font-bold tracking-[0.03em] uppercase">{item.name}</h3>
            <dl className="mt-4 space-y-2 text-[0.9rem]">
                {item.facts.map((fact) => (
                    <div key={fact.label}>
                        <dt className="inline text-moonlight/60">{fact.label}: </dt>
                        <dd className="inline">{fact.value}</dd>
                    </div>
                ))}
            </dl>
            <p className="mt-4 leading-relaxed text-moonlight/80">{item.description}</p>
            <p className="mt-4 text-[0.9rem] text-moonlight/70">
                <span className="font-semibold text-beam-glow">{copy.reliability}: </span>
                {item.reliability}
            </p>
            <p className="mt-auto border-t border-dashed border-moonlight/20 pt-4 text-[0.9rem]">
                <span className="font-semibold text-beam-glow">{copy.decision}: </span>
                {item.decision}
            </p>
        </article>
    );
}

export default function Atlas(props: Props) {
    const byCase = props.cases.map((c) => ({ c, sources: props.sources.filter((s) => s.places.includes(c.slug)) }));
    const listed = new Set(byCase.flatMap(({ sources }) => sources.map((s) => s.id)));
    const others = props.sources.filter((s) => !listed.has(s.id));

    return (
        <>
            <SeoHead />
            <PageCover eyebrow={t.origin.hub.atlasEyebrow} title={props.title} lead={props.subtitle}>
                <p className="mt-4 text-sm text-moonlight/70">
                    {props.summary}
                    {props.revision && ` · ${copy.revision(props.revision)}`}
                </p>
            </PageCover>

            <Section tone="dark" pattern="stars" labelledBy="mapa-atlas">
                <div className="grid gap-10 lg:grid-cols-[minmax(0,0.8fr)_minmax(0,1.2fr)] lg:items-center">
                    <div>
                        <Display as="h2" id="mapa-atlas">
                            {copy.mapTitle}
                        </Display>
                        <p className="mt-5 max-w-[46ch] text-lg leading-relaxed text-moonlight/80">{copy.mapLead}</p>
                    </div>
                    <LazyMap
                        markers={markers(props.cases)}
                        label={copy.mapLabel}
                        zoom={5}
                        className="aspect-[4/3] w-full"
                    />
                </div>
            </Section>

            <Section tone="light" wave labelledBy="casos">
                <Eyebrow>{copy.casesEyebrow}</Eyebrow>
                <Display as="h2" id="casos" className="mt-3">
                    {copy.casesTitle}
                </Display>
                <div className="mt-6 max-w-[62ch] space-y-4 text-lg leading-relaxed text-night/80">
                    {props.scope.map((p) => (
                        <p key={p}>{p}</p>
                    ))}
                </div>
                <Reveal stagger as="ul" className="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    {props.cases.map((item) => (
                        <li key={item.slug}>
                            <Stamp item={item} />
                        </li>
                    ))}
                </Reveal>
            </Section>

            <Section tone="dusk" wave labelledBy="metodo">
                <Eyebrow>{copy.methodEyebrow}</Eyebrow>
                <Display as="h2" id="metodo" className="mt-3">
                    {copy.methodTitle}
                </Display>
                <div className="mt-10 grid gap-12 lg:grid-cols-2">
                    <ul className="space-y-3 text-night/80">
                        {props.method.map((item) => (
                            <li key={item} className="flex gap-3 leading-relaxed">
                                <span aria-hidden className="mt-2.5 size-1.5 shrink-0 rounded-full bg-beam" />
                                {item}
                            </li>
                        ))}
                    </ul>
                    <div>
                        <h3 className="font-script text-2xl text-horizon">{copy.scaleTitle}</h3>
                        <dl className="mt-4 divide-y divide-night/10 border-y border-night/10">
                            {props.grades.map((g) => (
                                <div key={g.grade} className="flex items-center gap-5 py-3">
                                    <dt className="flex size-10 shrink-0 items-center justify-center rounded-full border-2 border-dashed border-horizon font-display font-extrabold text-horizon">
                                        {g.grade}
                                    </dt>
                                    <dd className="leading-snug text-night/80">{g.meaning}</dd>
                                </div>
                            ))}
                        </dl>
                    </div>
                </div>
            </Section>

            <Section tone="light" labelledBy="cronologia">
                <div className="grid gap-12 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)]">
                    <div>
                        <Eyebrow>{copy.timelineEyebrow}</Eyebrow>
                        <Display as="h2" id="cronologia" className="mt-3">
                            {copy.timelineTitle}
                        </Display>
                        <p className="mt-6 max-w-[48ch] leading-relaxed text-night/75">{props.timelineNote}</p>
                        <div className="mt-12">
                            <Eyebrow as="h3">{copy.questionsEyebrow}</Eyebrow>
                            <p className="mt-2 font-display text-xl font-extrabold tracking-[0.04em] uppercase">
                                {copy.questionsTitle}
                            </p>
                            <ul className="mt-5 space-y-4 text-night/80">
                                {props.crossQuestions.map((q) => (
                                    <li key={q} className="border-l-4 border-car pl-4 leading-relaxed">
                                        {q}
                                    </li>
                                ))}
                            </ul>
                        </div>
                    </div>
                    <Timeline
                        items={props.timeline.map((row) => ({ when: row.date, title: row.place, body: row.event }))}
                    />
                </div>
            </Section>

            <Section tone="dark" pattern="stars" wave labelledBy="candidatos">
                <Eyebrow tone="dark">{copy.candidatesEyebrow}</Eyebrow>
                <Display as="h2" id="candidatos" className="mt-3">
                    {copy.candidatesTitle}
                </Display>
                <p className="mt-6 max-w-[58ch] text-lg leading-relaxed text-moonlight/80">{props.candidatesNote}</p>
                <ul className="mt-10 grid gap-6 lg:grid-cols-3">
                    {props.candidates.map((item) => (
                        <li key={item.name}>
                            <Candidate item={item} />
                        </li>
                    ))}
                </ul>
            </Section>

            <Section tone="light" wave labelledBy="fontes">
                <Eyebrow>{copy.sourcesEyebrow}</Eyebrow>
                <Display as="h2" id="fontes" className="mt-3">
                    {copy.sourcesTitle}
                </Display>
                <p className="mt-4 font-script text-2xl text-horizon">{copy.sourcesCount(props.sources.length)}</p>
                <div className="mt-8">
                    <Accordion
                        items={[
                            ...byCase
                                .filter(({ sources }) => sources.length > 0)
                                .map(({ c, sources }) => ({
                                    title: `${c.name} · ${copy.sourcesCount(sources.length)}`,
                                    content: (
                                        <ol className="divide-y divide-night/10">
                                            {sources.map((s) => (
                                                <SourceRow key={s.id} source={s} />
                                            ))}
                                        </ol>
                                    ),
                                })),
                            ...(others.length > 0
                                ? [
                                      {
                                          title: `${copy.otherSources} · ${copy.sourcesCount(others.length)}`,
                                          content: (
                                              <ol className="divide-y divide-night/10">
                                                  {others.map((s) => (
                                                      <SourceRow key={s.id} source={s} />
                                                  ))}
                                              </ol>
                                          ),
                                      },
                                  ]
                                : []),
                        ]}
                    />
                </div>

                <div className="mt-20 grid gap-12 lg:grid-cols-[minmax(0,1.3fr)_minmax(0,0.7fr)]">
                    <div>
                        <h2 className="font-display text-xl font-extrabold tracking-[0.04em] uppercase">
                            {copy.creditsTitle}
                        </h2>
                        <p className="mt-2 max-w-[56ch] text-[0.95rem] text-night/70">{copy.creditsNote}</p>
                        <ul className="mt-6 grid grid-cols-2 gap-x-4 gap-y-6 xl:grid-cols-3">
                            {Object.values(props.images).map((credit) => (
                                <li key={credit.slug}>
                                    <CreditedImage
                                        credit={credit}
                                        caption={props.cases.find((c) => c.image === credit.slug)?.name ?? credit.title}
                                        sizes="(min-width: 1280px) 16rem, (min-width: 640px) 40vw, 92vw"
                                    />
                                </li>
                            ))}
                        </ul>
                    </div>
                    <div>
                        <h2 className="font-display text-xl font-extrabold tracking-[0.04em] uppercase">
                            {copy.limitationsTitle}
                        </h2>
                        <ul className="mt-6 space-y-3 text-[0.95rem] leading-relaxed text-night/75">
                            {props.limitations.map((item) => (
                                <li key={item} className="border-l-2 border-night/20 pl-4">
                                    {item}
                                </li>
                            ))}
                        </ul>
                    </div>
                </div>
            </Section>
        </>
    );
}

Atlas.layout = (page: ReactNode) => <PublicLayout>{page}</PublicLayout>;

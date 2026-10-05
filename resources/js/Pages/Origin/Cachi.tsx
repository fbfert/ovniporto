import { Link } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { SeoHead } from '@/Components/Layout/SeoHead';
import { ChapterIndex } from '@/Components/Origin/ChapterIndex';
import { CreditedImage } from '@/Components/Origin/CreditedImage';
import { OriginConcept, ORIGIN_CONCEPTS, type OriginConceptSlug } from '@/Components/Origin/OriginConcept';
import { SourceBadge } from '@/Components/Origin/SourceBadge';
import { Timeline } from '@/Components/Origin/Timeline';
import { VideoFacade } from '@/Components/Origin/VideoFacade';
import { Starfield } from '@/Components/Scene/Starfield';
import { Button } from '@/Components/Ui/Button';
import { ManifestPicture, type ManifestEntry } from '@/Components/Ui/Picture';
import { Reveal } from '@/Components/Ui/Reveal';
import { Section } from '@/Components/Ui/Section';
import { Display, Eyebrow } from '@/Components/Ui/Typography';
import manifest from '@/data/origin-images.json';
import { t } from '@/i18n/pt-BR';
import { PublicLayout } from '@/Layouts/PublicLayout';
import type { CachiChapter, CachiDossier, ImageCredits } from '@/types/origin';

const copy = t.origin.cachiPage;
const entries: Record<string, ManifestEntry | undefined> = manifest;

const isConcept = (slug: string | undefined): slug is OriginConceptSlug =>
    !!slug && (ORIGIN_CONCEPTS as readonly string[]).includes(slug);

/** Full-bleed aerial photo of the stone star, title over it, credit in the corner. */
function Cover({ dossier, images }: { dossier: CachiDossier; images: ImageCredits }) {
    const credit = images[dossier.cover];
    const entry = credit ? entries[credit.slug] : undefined;

    return (
        <section
            data-tone="dark"
            className="relative isolate flex min-h-[86svh] items-end overflow-hidden bg-night text-moonlight"
        >
            {credit && entry && (
                <ManifestPicture
                    base="/origin"
                    slug={credit.slug}
                    entry={entry}
                    alt={credit.alt}
                    priority
                    className="absolute inset-0 -z-20"
                    imgClassName="h-full w-full object-cover saturate-[0.8]"
                />
            )}
            <div className="absolute inset-0 -z-10 bg-[linear-gradient(180deg,rgb(6_17_33/0.35)_0%,rgb(6_17_33/0.7)_55%,var(--color-night)_100%)]" />
            <Starfield className="absolute inset-0 -z-10 opacity-50" density="low" />
            <div className="mx-auto w-full max-w-6xl px-5 pt-36 pb-16 sm:px-8 lg:pb-20">
                <Eyebrow tone="dark">{dossier.kicker}</Eyebrow>
                <Display as="h1" className="mt-4 max-w-[14ch] text-[clamp(2.2rem,1rem+6vw,6rem)]!">
                    {dossier.title}
                </Display>
                <p className="mt-6 max-w-[56ch] text-lg leading-relaxed text-moonlight/85">{dossier.lead}</p>
                <blockquote className="mt-10 max-w-[44ch] border-l-4 border-beam pl-5 font-script text-[clamp(1.5rem,1.2rem+1vw,2rem)] leading-snug text-beam-glow">
                    “{dossier.quote}”
                </blockquote>
                {credit && (
                    <p className="mt-10 text-[0.75rem] text-moonlight/60">
                        {copy.coverCredit}: {credit.author}, {credit.license} ·{' '}
                        <a
                            href={credit.sourceUrl}
                            target="_blank"
                            rel="noopener noreferrer"
                            className="underline underline-offset-2"
                        >
                            {t.origin.credit.source}
                        </a>
                    </p>
                )}
            </div>
        </section>
    );
}

function ChapterBody({ chapter, images }: { chapter: CachiChapter; images: ImageCredits }) {
    const blocks: ReactNode[] = [];

    if (chapter.kinds.length > 0) {
        blocks.push(
            <div key="kinds" className="flex flex-wrap gap-2">
                {chapter.kinds.map((kind) => (
                    <SourceBadge key={kind.kind} kind={kind} />
                ))}
            </div>,
        );
    }
    if (chapter.body.length > 0) {
        blocks.push(
            <div key="body" className="max-w-[62ch] space-y-5 text-lg leading-relaxed text-night/80">
                {chapter.body.map((p) => (
                    <p key={p}>{p}</p>
                ))}
            </div>,
        );
    }
    if (chapter.facts) {
        blocks.push(
            <div key="facts" className="rounded-[22px] bg-night-blue p-6 text-moonlight sm:p-8">
                <p className="font-script text-2xl text-beam-glow">{chapter.facts.title}</p>
                <dl className="mt-5 grid gap-x-8 gap-y-4 sm:grid-cols-2">
                    {chapter.facts.items.map((fact) => (
                        <div key={fact.label} className="border-t border-dashed border-moonlight/20 pt-3">
                            <dt className="text-[0.8rem] text-moonlight/60">{fact.label}</dt>
                            <dd className="mt-1 font-semibold">{fact.value}</dd>
                        </div>
                    ))}
                </dl>
            </div>,
        );
    }
    if (chapter.stats) {
        blocks.push(
            <dl key="stats" className="grid grid-cols-2 gap-6 sm:grid-cols-4">
                {chapter.stats.map((stat) => (
                    <div key={stat.label} className="border-t-2 border-beam pt-3">
                        <dt className="sr-only">{stat.label}</dt>
                        <dd>
                            <span className="block font-display text-[clamp(2rem,1.4rem+2vw,3rem)] leading-none font-extrabold text-horizon">
                                {stat.value}
                            </span>
                            <span aria-hidden className="mt-2 block text-[0.9rem] text-night/70">
                                {stat.label}
                            </span>
                        </dd>
                    </div>
                ))}
            </dl>,
        );
    }
    const photos = [chapter.image, ...(chapter.images ?? [])].filter(
        (slug): slug is string => !!slug && !!images[slug],
    );
    if (photos.length > 0) {
        blocks.push(
            <div key="photos" className={`grid gap-6 ${photos.length > 1 ? 'sm:grid-cols-2' : ''}`}>
                {photos.map((slug) => (
                    <CreditedImage
                        key={slug}
                        credit={images[slug]!}
                        sizes="(min-width: 1024px) 40rem, 92vw"
                        frameClassName={photos.length > 1 ? 'aspect-[4/3]' : 'aspect-[16/9]'}
                    />
                ))}
            </div>,
        );
    }
    if (isConcept(chapter.concept)) {
        blocks.push(
            <OriginConcept
                key="concept"
                slug={chapter.concept}
                sizes="(min-width: 1024px) 40rem, 92vw"
                className="relative aspect-[16/9] rounded-[18px]"
            />,
        );
    }
    if (chapter.cases) {
        blocks.push(
            <ul key="cases" className="grid gap-5 sm:grid-cols-2">
                {chapter.cases.map((item) => (
                    <li
                        key={item.title}
                        className="flex overflow-hidden rounded-[18px] bg-moonlight ring-1 ring-night/10"
                    >
                        <span className="flex w-20 shrink-0 items-center justify-center border-r-2 border-dashed border-night/15 bg-horizon/8 px-2 text-center font-display text-[0.72rem] leading-tight font-bold tracking-[0.04em] text-horizon uppercase">
                            {item.date}
                        </span>
                        <div className="p-5">
                            <h3 className="font-display text-[0.95rem] leading-tight font-bold tracking-[0.03em] uppercase">
                                {item.title}
                            </h3>
                            <p className="mt-2 text-[0.95rem] leading-relaxed text-night/75">{item.summary}</p>
                            <div className="mt-3">
                                <SourceBadge kind={item.kind} detail={item.detail} />
                            </div>
                        </div>
                    </li>
                ))}
            </ul>,
        );
    }
    if (chapter.document) {
        blocks.push(
            <div key="document" className="rounded-[22px] border-2 border-dashed border-beam bg-beam/8 p-6 sm:p-8">
                <p className="font-display text-lg font-extrabold tracking-[0.04em] uppercase">
                    {chapter.document.title}
                </p>
                <p className="mt-2 max-w-[56ch] leading-relaxed text-night/80">{chapter.document.body}</p>
                <div className="mt-5">
                    <Button href={chapter.document.url} variant="ghost" external>
                        {chapter.document.action}
                    </Button>
                </div>
            </div>,
        );
    }
    if (chapter.milestones) {
        blocks.push(
            <div key="milestones">
                <Timeline items={chapter.milestones.map((m) => ({ when: m.year, title: m.title, body: m.body }))} />
            </div>,
        );
    }
    if (chapter.people) {
        blocks.push(
            <ul key="people" className="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                {chapter.people.map((person) => (
                    <li key={person.name} className="border-t-2 border-horizon pt-4">
                        <h3 className="font-display text-base leading-tight font-bold tracking-[0.03em] uppercase">
                            {person.name}
                        </h3>
                        <p className="mt-2 text-[0.95rem] leading-relaxed text-night/75">{person.role}</p>
                    </li>
                ))}
            </ul>,
        );
    }
    if (chapter.timeline) {
        blocks.push(
            <Timeline
                key="timeline"
                items={chapter.timeline.map((m) => ({ when: m.year, title: m.title, body: m.body }))}
            />,
        );
    }
    if (chapter.gallery) {
        blocks.push(
            <ul key="gallery" className="grid gap-8 sm:grid-cols-2">
                {chapter.gallery
                    .filter((item) => images[item.image])
                    .map((item, i) => (
                        <li key={item.image} className={i % 2 === 0 ? 'sm:-rotate-1' : 'sm:translate-y-8 sm:rotate-1'}>
                            <CreditedImage
                                credit={images[item.image]!}
                                caption={item.caption}
                                sizes="(min-width: 640px) 22rem, 92vw"
                            />
                        </li>
                    ))}
            </ul>,
        );
    }
    if (chapter.videos) {
        blocks.push(
            <div
                key="videos"
                data-tone="dark"
                className="-mx-5 rounded-none bg-night px-5 py-10 text-moonlight sm:mx-0 sm:rounded-[26px] sm:p-10"
            >
                <ul className="grid gap-10 md:grid-cols-2">
                    {chapter.videos.map((video) => (
                        <li key={video.url}>
                            <VideoFacade video={video} />
                        </li>
                    ))}
                </ul>
            </div>,
        );
    }
    if (chapter.sources) {
        blocks.push(
            <ol key="sources" className="divide-y divide-night/10 border-y border-night/10">
                {chapter.sources.map((source) => (
                    <li
                        key={source.url + source.title}
                        className="flex flex-col gap-2 py-4 sm:flex-row sm:items-baseline sm:gap-6"
                    >
                        <span className="w-24 shrink-0 text-[0.85rem] text-night/60">{source.date}</span>
                        <span className="flex-1">
                            <a
                                href={source.url}
                                target="_blank"
                                rel="noopener noreferrer"
                                className="font-semibold underline decoration-night/30 underline-offset-4 hover:decoration-horizon"
                            >
                                {source.title}
                            </a>
                            <span className="block text-[0.9rem] text-night/65">{source.publisher}</span>
                        </span>
                        <SourceBadge kind={source.kind} />
                    </li>
                ))}
            </ol>,
        );
    }
    if (chapter.note) {
        blocks.push(
            <p
                key="note"
                className="max-w-[60ch] border-l-4 border-car pl-4 text-[0.95rem] leading-relaxed text-night/70 italic"
            >
                {chapter.note}
            </p>,
        );
    }

    return <div className="mt-8 flex flex-col gap-8">{blocks}</div>;
}

export default function Cachi({ dossier, images }: { dossier: CachiDossier; images: ImageCredits }) {
    return (
        <>
            <SeoHead />
            <Cover dossier={dossier} images={images} />

            <Section tone="light">
                <div className="grid gap-12 lg:grid-cols-[15rem_minmax(0,1fr)] lg:gap-16">
                    <ChapterIndex chapters={dossier.chapters.map((c) => ({ id: c.id, title: c.title }))} />
                    <div className="flex flex-col gap-24">
                        {dossier.chapters.map((chapter) => (
                            <section
                                key={chapter.id}
                                aria-labelledby={`${chapter.id}-titulo`}
                                id={chapter.id}
                                className="scroll-mt-28"
                            >
                                <Reveal>
                                    <Eyebrow>{chapter.eyebrow}</Eyebrow>
                                    <Display
                                        as="h2"
                                        id={`${chapter.id}-titulo`}
                                        className="mt-3 max-w-[20ch] text-[clamp(1.6rem,0.9rem+3vw,3.4rem)]!"
                                    >
                                        {chapter.title}
                                    </Display>
                                    <ChapterBody chapter={chapter} images={images} />
                                </Reveal>
                            </section>
                        ))}
                    </div>
                </div>
            </Section>

            <Section tone="dark" pattern="stars" wave labelledBy="epilogo">
                <h2 id="epilogo" className="sr-only">
                    Epílogo
                </h2>
                <ol className="mx-auto max-w-3xl space-y-2 text-center">
                    {dossier.epilogue.map((line, i) => (
                        <li
                            key={line}
                            className={
                                i === dossier.epilogue.length - 1
                                    ? 'pt-4 font-display text-[clamp(1.6rem,1rem+2.6vw,3.4rem)] leading-tight font-extrabold tracking-[0.04em] text-beam-glow uppercase'
                                    : 'font-script text-[clamp(1.5rem,1.1rem+1.4vw,2.4rem)] text-moonlight/85'
                            }
                        >
                            {line}
                        </li>
                    ))}
                </ol>
                <div className="mx-auto mt-16 flex max-w-3xl flex-col items-center gap-5 border-t border-moonlight/15 pt-12 text-center">
                    <p className="font-display text-xl font-extrabold tracking-[0.04em] uppercase">{copy.nextTitle}</p>
                    <p className="max-w-[52ch] leading-relaxed text-moonlight/75">{copy.nextBody}</p>
                    <div className="flex flex-wrap justify-center gap-3">
                        <Button href="/origem/atlas">{copy.nextCta}</Button>
                        <Link
                            href="/origem"
                            className="inline-flex min-h-11 items-center px-3 font-semibold text-moonlight/80 underline underline-offset-4 hover:text-beam-glow"
                        >
                            {t.origin.backToOrigin}
                        </Link>
                    </div>
                </div>
            </Section>
        </>
    );
}

Cachi.layout = (page: ReactNode) => <PublicLayout>{page}</PublicLayout>;

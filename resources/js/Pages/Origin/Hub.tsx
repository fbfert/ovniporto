import { Link } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { PageCover } from '@/Components/Content/PageCover';
import { Prose } from '@/Components/Content/Prose';
import { SeoHead } from '@/Components/Layout/SeoHead';
import { CreditedImage } from '@/Components/Origin/CreditedImage';
import { OriginConcept, type OriginConceptSlug } from '@/Components/Origin/OriginConcept';
import { Button } from '@/Components/Ui/Button';
import { Polaroid } from '@/Components/Ui/Polaroid';
import { Reveal } from '@/Components/Ui/Reveal';
import { Section } from '@/Components/Ui/Section';
import { Display, Eyebrow } from '@/Components/Ui/Typography';
import { WaitlistForm } from '@/Components/Home/WaitlistForm';
import { t } from '@/i18n/pt-BR';
import { PublicLayout } from '@/Layouts/PublicLayout';
import type { ImageCredit } from '@/types/origin';

const copy = t.origin.hub;

interface Props {
    relatoHtml: string | null;
    cachi: { chapters: number; cover: ImageCredit | null };
    atlas: { cases: number; countries: number; sources: number };
}

/** "De Cachi a Lages": the founders' trip, told next to the stone star that started it. */
function Story({ cover }: { cover: ImageCredit | null }) {
    return (
        <Section tone="light" labelledBy="de-cachi-a-lages">
            <div className="grid items-center gap-14 md:grid-cols-[minmax(0,1.1fr)_minmax(0,0.9fr)] lg:gap-24">
                <Reveal>
                    <Eyebrow>{copy.storyEyebrow}</Eyebrow>
                    <Display as="h2" id="de-cachi-a-lages" className="mt-3">
                        {copy.storyTitle}
                    </Display>
                    <div className="mt-8 max-w-[56ch] space-y-5 text-lg leading-relaxed text-night/80">
                        {copy.story.map((paragraph) => (
                            <p key={paragraph}>{paragraph}</p>
                        ))}
                    </div>
                </Reveal>
                <Reveal className="relative mx-auto w-full max-w-sm md:mx-0">
                    {cover && (
                        <CreditedImage
                            credit={cover}
                            sizes="(min-width: 768px) 24rem, 85vw"
                            frameClassName="aspect-[4/5]"
                            className="rotate-2"
                        />
                    )}
                </Reveal>
            </div>
        </Section>
    );
}

/** "O relato do / O carro amarelo": the founders' text once it exists, an honest wait until then. */
function Relato({ html }: { html: string | null }) {
    return (
        <Section tone="dark" pattern="stars" wave labelledBy="carro-amarelo">
            <div className="grid items-center gap-14 md:grid-cols-[minmax(0,0.85fr)_minmax(0,1.15fr)] lg:gap-24">
                <Reveal className="mx-auto w-full max-w-sm md:mx-0">
                    <Polaroid
                        rotate={-3}
                        tape="top"
                        imageClassName="aspect-[4/5]"
                        art={
                            <OriginConcept
                                slug="niva-abduction"
                                sizes="(min-width: 768px) 24rem, 85vw"
                                className="absolute inset-0"
                            />
                        }
                        caption={t.originSection.polaroid}
                    />
                </Reveal>
                <Reveal>
                    <Eyebrow tone="dark">{copy.relatoEyebrow}</Eyebrow>
                    <Display as="h2" id="carro-amarelo" className="mt-3">
                        {copy.relatoTitle}
                    </Display>
                    {html ? (
                        <Prose html={html} className="mt-8 text-moonlight/90" />
                    ) : (
                        <>
                            <p className="mt-6 max-w-[46ch] text-lg leading-relaxed text-moonlight/80">
                                {copy.relatoPending}
                            </p>
                            <div className="mt-8 max-w-lg">
                                <p className="mb-3 font-script text-2xl text-beam-glow">{copy.notify}</p>
                                <WaitlistForm source="origem" />
                            </div>
                        </>
                    )}
                </Reveal>
            </div>
        </Section>
    );
}

function Door({
    href,
    eyebrow,
    title,
    body,
    cta,
    art,
}: {
    href: string;
    eyebrow: string;
    title: string;
    body: string;
    cta: string;
    art: ReactNode;
}) {
    return (
        <Link
            href={href}
            className="group/door flex h-full flex-col overflow-hidden rounded-[22px] bg-night text-moonlight ticket-punch [--punch-y:62%] focus-visible:outline-beam"
        >
            <div className="relative aspect-[16/10] overflow-hidden">{art}</div>
            <div aria-hidden className="mx-6 border-t-2 border-dashed border-moonlight/25" />
            <div className="flex flex-1 flex-col p-6 sm:p-8">
                <span className="font-script text-2xl text-beam-glow">{eyebrow}</span>
                <span className="mt-1 font-display text-[clamp(1.3rem,1rem+1.2vw,1.9rem)] leading-tight font-extrabold tracking-[0.04em] uppercase">
                    {title}
                </span>
                <span className="mt-3 max-w-[44ch] leading-relaxed text-moonlight/75">{body}</span>
                <span className="mt-auto inline-flex items-center gap-2 pt-6 font-semibold text-beam-glow group-hover/door:underline">
                    {cta}
                    <span aria-hidden className="transition-transform duration-300 group-hover/door:translate-x-1">
                        →
                    </span>
                </span>
            </div>
        </Link>
    );
}

function Doors({ cachi, atlas }: Pick<Props, 'cachi' | 'atlas'>) {
    const art = (slug: OriginConceptSlug) => (
        <OriginConcept
            slug={slug}
            sizes="(min-width: 1024px) 40vw, 90vw"
            className="absolute inset-0 transition-transform duration-500 ease-snap group-hover/door:scale-[1.03]"
        />
    );

    return (
        <Section tone="dusk" wave labelledBy="mais-longe">
            <Display as="h2" id="mais-longe" className="max-w-[16ch]">
                {copy.doorsTitle}
            </Display>
            <div className="mt-12 grid gap-8 md:grid-cols-2">
                <Door
                    href="/origem/cachi"
                    eyebrow={copy.cachiEyebrow}
                    title={copy.cachiTitle}
                    body={copy.cachiBody(cachi.chapters)}
                    cta={copy.cachiCta}
                    art={art('stone-star-night')}
                />
                <Door
                    href="/origem/atlas"
                    eyebrow={copy.atlasEyebrow}
                    title={copy.atlasTitle}
                    body={copy.atlasBody(atlas.cases, atlas.countries, atlas.sources)}
                    cta={copy.atlasCta}
                    art={art('atlas-globe')}
                />
            </div>
        </Section>
    );
}

export default function Hub({ relatoHtml, cachi, atlas }: Props) {
    return (
        <>
            <SeoHead />
            <PageCover eyebrow={copy.eyebrow} title={copy.title} lead={copy.lead} />
            <Story cover={cachi.cover} />
            <Relato html={relatoHtml} />
            <Doors cachi={cachi} atlas={atlas} />
            <Section tone="light" labelledBy="relatar">
                <div className="flex flex-col items-start gap-6 sm:flex-row sm:items-center sm:justify-between">
                    <p
                        id="relatar"
                        className="font-script text-[clamp(1.8rem,1.3rem+1.8vw,2.8rem)] leading-tight text-horizon"
                    >
                        {copy.closing}
                    </p>
                    <Button href="/relatar" size="lg">
                        {copy.report}
                    </Button>
                </div>
            </Section>
        </>
    );
}

Hub.layout = (page: ReactNode) => <PublicLayout>{page}</PublicLayout>;

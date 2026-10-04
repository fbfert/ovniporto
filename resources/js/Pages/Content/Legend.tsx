import { useId, useState, type ReactNode } from 'react';
import { PageCover } from '@/Components/Content/PageCover';
import { Prose } from '@/Components/Content/Prose';
import { WaitlistForm } from '@/Components/Home/WaitlistForm';
import { ChevronDownIcon } from '@/Components/Icons';
import { SeoHead } from '@/Components/Layout/SeoHead';
import { Button } from '@/Components/Ui/Button';
import { ConceptImage } from '@/Components/Ui/ConceptImage';
import { NightSkyArt, Polaroid } from '@/Components/Ui/Polaroid';
import { Reveal } from '@/Components/Ui/Reveal';
import { Section } from '@/Components/Ui/Section';
import { Badge, Display, Eyebrow } from '@/Components/Ui/Typography';
import { CACHI_TIMELINE } from '@/data/cachi';
import { t } from '@/i18n/pt-BR';
import { PublicLayout } from '@/Layouts/PublicLayout';

const copy = t.legendPage;

/** "Saiba mais sobre Cachi": a disclosure button that opens the fixed timeline. */
function CachiTimeline() {
    const [open, setOpen] = useState(false);
    const panelId = useId();
    return (
        <div className="mt-10">
            <Button
                variant="secondary"
                aria-expanded={open}
                aria-controls={panelId}
                onClick={() => setOpen(!open)}
                iconRight={
                    <ChevronDownIcon
                        size="1.05rem"
                        className={`transition-transform duration-200 ease-snap ${open ? 'rotate-180' : ''}`}
                    />
                }
            >
                {open ? copy.timelineHide : copy.timelineToggle}
            </Button>
            <div
                id={panelId}
                inert={!open}
                className={`grid transition-[grid-template-rows,opacity] duration-[260ms] ease-snap ${open ? 'grid-rows-[1fr] opacity-100' : 'grid-rows-[0fr] opacity-0'}`}
            >
                <div className="overflow-hidden">
                    <h3 className="mt-10 text-[0.7rem] font-semibold tracking-[0.12em] text-night/60 uppercase">
                        {copy.timelineTitle}
                    </h3>
                    <ol className="relative mt-6 ml-3 border-l-2 border-dashed border-horizon/40">
                        {CACHI_TIMELINE.map((milestone) => (
                            <li key={milestone.when} className="relative pb-8 pl-8 last:pb-0">
                                <span
                                    aria-hidden
                                    className="absolute top-1.5 -left-[7px] size-3 rounded-full bg-beam shadow-[0_0_0_4px_var(--color-moonlight)]"
                                />
                                <p className="font-display text-lg font-extrabold tracking-[0.04em] text-horizon uppercase">
                                    {milestone.when}
                                </p>
                                <p className="mt-1 max-w-[56ch] leading-relaxed text-night/80">{milestone.text}</p>
                            </li>
                        ))}
                    </ol>
                </div>
            </div>
        </div>
    );
}

function PendingLegend() {
    return (
        <div className="grid items-center gap-14 md:grid-cols-[minmax(0,0.9fr)_minmax(0,1.1fr)] lg:gap-24">
            <Reveal className="mx-auto w-full max-w-sm md:mx-0">
                <Polaroid
                    rotate={-3}
                    tape="top"
                    imageClassName="aspect-[4/5]"
                    art={
                        <ConceptImage
                            slug="yellow-car"
                            sizes="(min-width: 768px) 24rem, 85vw"
                            className="absolute inset-0"
                            badgeClassName="bottom-3 left-3"
                        />
                    }
                    caption={copy.pendingCaption}
                />
            </Reveal>
            <Reveal>
                <Eyebrow tone="dark">{copy.carEyebrow}</Eyebrow>
                <Display as="h2" id="carro-amarelo" className="mt-3">
                    {copy.carTitle}
                </Display>
                <Badge tone="neutral" className="mt-6">
                    {t.legend.pending}
                </Badge>
                <p className="mt-6 max-w-[46ch] text-lg leading-relaxed text-moonlight/80">{copy.pendingLead}</p>
                <div className="mt-8 max-w-lg">
                    <p className="mb-3 font-script text-2xl text-beam-glow">{copy.notify}</p>
                    <WaitlistForm source="lenda" />
                </div>
            </Reveal>
        </div>
    );
}

function PublishedLegend({ html }: { html: string }) {
    return (
        <Reveal className="mx-auto max-w-[68ch]">
            <Eyebrow tone="dark">{copy.carEyebrow}</Eyebrow>
            <Display as="h2" id="carro-amarelo" className="mt-3 mb-10">
                {copy.carTitle}
            </Display>
            <Prose html={html} className="dropcap text-moonlight/90" />
        </Reveal>
    );
}

export default function Legend({ legendHtml }: { legendHtml: string | null }) {
    return (
        <>
            <SeoHead />
            <PageCover eyebrow={copy.eyebrow} title={copy.title} lead={copy.lead} />

            <Section tone="light" labelledBy="origem">
                <div className="grid gap-14 lg:grid-cols-[minmax(0,1.1fr)_minmax(0,0.9fr)] lg:gap-20">
                    <Reveal>
                        <Eyebrow>{copy.originEyebrow}</Eyebrow>
                        <Display as="h2" id="origem" className="mt-3">
                            {copy.originTitle}
                        </Display>
                        <div className="mt-8 space-y-5 text-lg leading-relaxed text-night/80">
                            {copy.origin.map((paragraph) => (
                                <p key={paragraph} className="max-w-[58ch]">
                                    {paragraph}
                                </p>
                            ))}
                        </div>
                        <CachiTimeline />
                    </Reveal>
                    <div className="relative mx-auto grid w-full max-w-md grid-cols-2 gap-6 pt-4 lg:mx-0">
                        <Polaroid
                            rotate={-4}
                            tape="top"
                            art={<NightSkyArt label="Cachi" seed={8} />}
                            caption={copy.photoPending[0]}
                        />
                        <Polaroid
                            rotate={3}
                            tape="corner"
                            className="mt-14"
                            art={<NightSkyArt seed={13} />}
                            caption={copy.photoPending[1]}
                        />
                    </div>
                </div>
            </Section>

            <Section tone="dark" pattern="stars" wave labelledBy="carro-amarelo">
                {legendHtml ? <PublishedLegend html={legendHtml} /> : <PendingLegend />}
            </Section>

            <Section tone="light" wave innerClassName="text-center">
                <p className="font-script text-[clamp(1.8rem,1.3rem+2vw,2.6rem)] text-horizon">{copy.closing}</p>
                <div className="mt-6">
                    <Button href="/relatar" size="lg">
                        {copy.report}
                    </Button>
                </div>
            </Section>
        </>
    );
}

Legend.layout = (page: ReactNode) => <PublicLayout>{page}</PublicLayout>;

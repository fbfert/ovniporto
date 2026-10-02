import { Button } from '@/Components/Ui/Button';
import { ConceptImage } from '@/Components/Ui/ConceptImage';
import { hasConcept } from '@/Components/Ui/Picture';
import { Reveal, RevealItem } from '@/Components/Ui/Reveal';
import { Section } from '@/Components/Ui/Section';
import { Badge, Display, Eyebrow } from '@/Components/Ui/Typography';
import { t } from '@/i18n/pt-BR';
import type { PlaceSpace } from '@/types';

/** Section 05. The runway as it honestly is: a plan, phase by phase. */
export function PlaceSection({ lead, spaces }: { lead: string; spaces: PlaceSpace[] }) {
    return (
        <Section tone="light" labelledBy="o-lugar" innerClassName="pt-[clamp(3rem,2rem+4vw,6rem)]!">
            <div className="grid gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(0,1.1fr)] lg:items-end">
                <div>
                    <Eyebrow>{t.place.eyebrow}</Eyebrow>
                    <Display as="h2" id="o-lugar" className="mt-3">
                        {t.place.title}
                    </Display>
                </div>
                <p className="max-w-[52ch] text-lg leading-relaxed text-night/80 lg:pb-2">{lead}</p>
            </div>

            <Reveal stagger className="mt-12 grid gap-5 md:grid-cols-2">
                <RevealItem>
                    <figure>
                        <div className="flex aspect-[16/10] flex-col items-center justify-center gap-3 rounded-[22px] border-2 border-dashed border-night/25 bg-night/[0.03] p-6 text-center">
                            <svg aria-hidden viewBox="0 0 48 48" className="size-12 text-horizon" fill="none">
                                <rect
                                    x="6"
                                    y="12"
                                    width="36"
                                    height="26"
                                    rx="5"
                                    stroke="currentColor"
                                    strokeWidth="2.5"
                                />
                                <circle cx="24" cy="25" r="7" stroke="currentColor" strokeWidth="2.5" />
                                <path
                                    d="M17 12l3-5h8l3 5"
                                    stroke="currentColor"
                                    strokeWidth="2.5"
                                    strokeLinejoin="round"
                                />
                            </svg>
                            <p className="font-script text-2xl text-horizon">{t.place.todayEmpty}</p>
                        </div>
                        <figcaption className="mt-3 px-1 text-sm font-semibold">{t.place.today}</figcaption>
                    </figure>
                </RevealItem>
                <RevealItem>
                    <figure>
                        <ConceptImage
                            slug="overview"
                            sizes="(min-width: 768px) 50vw, 100vw"
                            className="group aspect-[16/10] rounded-[22px] bg-night"
                            imgClassName="h-full w-full object-cover object-[60%_50%] transition-transform duration-700 ease-snap [@media(hover:hover)]:group-hover:scale-[1.03]"
                            badgeClassName="top-4 right-4"
                        />
                        <figcaption className="mt-3 px-1 text-sm font-semibold">{t.place.future}</figcaption>
                    </figure>
                </RevealItem>
            </Reveal>

            <h3 className="mt-16 text-[0.7rem] font-semibold tracking-[0.12em] text-night/60 uppercase">
                {t.place.spacesLabel}
            </h3>
            <div
                tabIndex={0}
                role="region"
                aria-label={t.place.spacesLabel}
                className="-mx-5 mt-4 [scrollbar-width:none] overflow-x-auto overscroll-x-contain [mask-image:linear-gradient(to_right,transparent,#000_1.25rem,#000_calc(100%-3rem),transparent)] px-5 pb-6 sm:-mx-8 sm:px-8"
            >
                <ol className="flex snap-x snap-mandatory gap-4">
                    {spaces.map((space, i) => (
                        <li
                            key={space.slug}
                            className={`flex w-[min(78vw,17.5rem)] shrink-0 snap-start flex-col overflow-hidden rounded-[22px] p-6 pt-0 ring-1 ${
                                space.phase === 1 ? 'bg-beam/10 ring-beam/50' : 'bg-night/[0.03] ring-night/12'
                            }`}
                        >
                            {hasConcept(space.concept) ? (
                                <ConceptImage
                                    slug={space.concept}
                                    sizes="18rem"
                                    className="-mx-6 mb-5 aspect-[4/3] bg-night"
                                />
                            ) : (
                                <div className="-mx-6 mb-5 flex aspect-[4/3] items-center justify-center border-b-2 border-dashed border-night/15 bg-night/[0.03]">
                                    <p className="font-script text-xl text-horizon">{t.place.conceptPending}</p>
                                </div>
                            )}
                            <Display
                                as="span"
                                outlined
                                className={`text-5xl leading-none ${space.phase === 1 ? 'text-beam' : 'text-horizon'}`}
                            >
                                {String(i + 1).padStart(2, '0')}
                            </Display>
                            <p className="mt-5 font-display text-[1.05rem] leading-tight font-bold tracking-[0.03em] uppercase">
                                {space.name}
                            </p>
                            <p className="mt-2 text-[0.95rem] leading-snug text-night/70">{space.role}</p>
                            <p className="mt-auto flex flex-wrap gap-2 pt-5">
                                <Badge tone={space.phase === 1 ? 'beam' : 'neutral'}>
                                    {t.place.phase(space.phase)}
                                </Badge>
                                <Badge tone="car">{t.place.status[space.status]}</Badge>
                            </p>
                        </li>
                    ))}
                </ol>
            </div>

            <div className="mt-6">
                <Button href="/o-lugar" variant="secondary">
                    {t.place.more}
                </Button>
            </div>
        </Section>
    );
}

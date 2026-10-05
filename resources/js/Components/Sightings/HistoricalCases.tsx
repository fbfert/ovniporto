import { Link } from '@inertiajs/react';
import { ConceptImage } from '@/Components/Ui/ConceptImage';
import { hasConcept } from '@/Components/Ui/Picture';
import { Reveal, RevealItem } from '@/Components/Ui/Reveal';
import { Section } from '@/Components/Ui/Section';
import { Display, Eyebrow } from '@/Components/Ui/Typography';
import { t } from '@/i18n/pt-BR';
import type { HistoricalCaseCard, HistoricalSection } from '@/types/historical';

const copy = t.historical;

/** The case's AI reconstruction, always with the "ilustração" badge; nothing while it is missing. */
export function HistoricalCaseArt({
    image,
    sizes,
    priority = false,
    className = '',
}: {
    image: string;
    sizes: string;
    priority?: boolean;
    className?: string;
}) {
    if (!hasConcept(image)) return null;

    return (
        <ConceptImage
            slug={image}
            badge={t.concept.illustration}
            sizes={sizes}
            priority={priority}
            className={`aspect-[3/2] bg-night ${className}`}
            imgClassName="h-full w-full object-cover"
        />
    );
}

/** An archive card: the reconstruction, the date as a stamp, the place in script, the title. */
function CaseCard({ item }: { item: HistoricalCaseCard }) {
    return (
        <Link
            href={`/mapa/casos/${item.slug}`}
            className="group/case flex h-full flex-col overflow-hidden rounded-[22px] bg-moonlight ring-1 ring-night/12 transition-shadow duration-200 hover:ring-2 hover:ring-horizon"
        >
            <HistoricalCaseArt image={item.image} sizes="(min-width: 1024px) 24rem, (min-width: 640px) 45vw, 92vw" />
            <div className="flex flex-1 flex-col border-t-2 border-dashed border-night/15 p-5">
                <span className="font-display text-[0.8rem] font-bold tracking-[0.06em] text-horizon uppercase">
                    {item.date}
                </span>
                <span className="mt-1 font-script text-lg leading-snug text-night/70">{item.place}</span>
                <h4 className="mt-3 font-display text-[1.02rem] leading-tight font-extrabold tracking-[0.03em] uppercase group-hover/case:underline">
                    {item.title}
                </h4>
                <p className="mt-3 line-clamp-3 text-[0.95rem] leading-relaxed text-night/75">{item.summary}</p>
            </div>
        </Link>
    );
}

/**
 * "Casos históricos" of /mapa: researched cases, kept apart from the community's reports, grouped
 * as Santa Catarina, the rest of Brazil and the world.
 */
export function HistoricalCases({ section }: { section: HistoricalSection }) {
    return (
        <Section tone="light" wave labelledBy="casos-historicos">
            <Eyebrow>{section.eyebrow}</Eyebrow>
            <Display as="h2" id="casos-historicos" className="mt-3">
                {section.title}
            </Display>
            <p className="mt-6 max-w-[60ch] text-lg leading-relaxed text-night/80">{section.intro}</p>
            <p className="mt-4 max-w-[60ch] text-[0.95rem] leading-relaxed text-night/65">{copy.illustrationNote}</p>

            <div className="mt-14 flex flex-col gap-16">
                {section.groups.map((group) => (
                    <section key={group.region} aria-labelledby={`historicos-${group.region}`}>
                        <div className="flex flex-wrap items-baseline gap-x-4 gap-y-1 border-b-2 border-dashed border-night/15 pb-3">
                            <h3
                                id={`historicos-${group.region}`}
                                className="font-display text-xl font-extrabold tracking-[0.04em] uppercase"
                            >
                                {group.label}
                            </h3>
                            <span className="font-script text-xl text-horizon">{copy.count(group.cases.length)}</span>
                        </div>
                        <Reveal
                            stagger
                            as="ul"
                            className="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
                        >
                            {group.cases.map((item) => (
                                <RevealItem as="li" key={item.slug}>
                                    <CaseCard item={item} />
                                </RevealItem>
                            ))}
                        </Reveal>
                    </section>
                ))}
            </div>

            {section.note && (
                <p className="mt-16 max-w-[64ch] border-l-4 border-car pl-4 text-[0.95rem] leading-relaxed text-night/70 italic">
                    {section.note}
                </p>
            )}
        </Section>
    );
}

import { Link } from '@inertiajs/react';
import { AraucariaShape } from '@/Components/Scene/Art';
import { Button } from '@/Components/Ui/Button';
import { ContentImage } from '@/Components/Ui/Picture';
import { Reveal, RevealItem } from '@/Components/Ui/Reveal';
import { Section } from '@/Components/Ui/Section';
import { Badge, Display, Eyebrow } from '@/Components/Ui/Typography';
import { t } from '@/i18n/pt-BR';
import type { PartnerCard } from '@/types';

/** Section 08. Partners who agreed to be listed; until then, an invitation instead of a hole. */
export function RegionSection({ partners, contactEmail }: { partners: PartnerCard[]; contactEmail: string }) {
    const mailto = `mailto:${contactEmail}?subject=${encodeURIComponent('Quero aparecer no OVNIPORTO')}`;

    return (
        <Section tone="dusk" labelledBy="regiao">
            <div className="flex flex-col gap-6 md:flex-row md:items-end md:justify-between">
                <div>
                    <Eyebrow>{t.region.eyebrow}</Eyebrow>
                    <Display as="h3" id="regiao" className="mt-3">
                        {t.region.title}
                    </Display>
                </div>
                <Button href="/regiao" variant="secondary">
                    {t.region.all}
                </Button>
            </div>

            {partners.length > 0 ? (
                <Reveal stagger as="ul" className="mt-12 grid gap-5 md:grid-cols-3">
                    {partners.map((partner) => (
                        <RevealItem as="li" key={partner.slug}>
                            <Link
                                href={`/regiao#${partner.slug}`}
                                className="group flex items-center gap-4 rounded-[22px] bg-moonlight p-3 ring-1 ring-night/10 transition-shadow duration-300 ease-snap hover:shadow-lift"
                            >
                                <div className="size-24 shrink-0 overflow-hidden rounded-2xl bg-night-blue">
                                    {partner.cover && (
                                        <ContentImage
                                            src={partner.cover}
                                            alt=""
                                            sizes="6rem"
                                            className="h-full w-full"
                                        />
                                    )}
                                </div>
                                <div className="min-w-0">
                                    <p className="truncate font-display text-base font-bold tracking-[0.03em] uppercase">
                                        {partner.name}
                                    </p>
                                    <p className="mt-1 text-sm text-night/70">
                                        {t.region.types[partner.type] ?? partner.type} · {partner.city}
                                    </p>
                                    {partner.isExample && (
                                        <Badge tone="neutral" className="mt-2">
                                            {t.region.example}
                                        </Badge>
                                    )}
                                </div>
                            </Link>
                        </RevealItem>
                    ))}
                </Reveal>
            ) : (
                <Reveal className="mt-12">
                    <div className="relative overflow-hidden rounded-[26px] bg-moonlight px-6 py-12 ring-1 ring-night/10 sm:px-12">
                        <svg
                            aria-hidden
                            viewBox="0 0 600 200"
                            className="absolute right-0 bottom-0 h-full w-auto text-night/[0.07]"
                            preserveAspectRatio="xMaxYMax meet"
                        >
                            <g transform="translate(420 200)">
                                <AraucariaShape height={190} seed={5} fill="currentColor" />
                            </g>
                            <g transform="translate(540 200)">
                                <AraucariaShape height={140} seed={8} fill="currentColor" />
                            </g>
                        </svg>
                        <p className="relative max-w-[30ch] font-script text-[clamp(1.8rem,1.4rem+1.6vw,2.6rem)] leading-tight text-horizon">
                            {t.region.empty}
                        </p>
                        <div className="relative mt-8">
                            <Button href={mailto} variant="primary">
                                {t.region.join}
                            </Button>
                        </div>
                    </div>
                </Reveal>
            )}
        </Section>
    );
}

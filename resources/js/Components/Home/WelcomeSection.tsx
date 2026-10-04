import { CountUp } from '@/Components/Ui/CountUp';
import { BeamIcon, CompassIcon, PinIcon, StarIcon } from '@/Components/Icons';
import { InfoCard } from '@/Components/Ui/InfoCard';
import { Reveal } from '@/Components/Ui/Reveal';
import { Section } from '@/Components/Ui/Section';
import { Badge, Display, Eyebrow } from '@/Components/Ui/Typography';
import { t } from '@/i18n/pt-BR';

/**
 * Section 02. The wordmark set at poster scale, then the facts laid out like the
 * fields of a boarding pass, separated by perforations.
 */
/** One cell of the welcome facts strip: dashed separators between the four facts. */
const cell = (i: number) =>
    `relative p-5 sm:p-6 ${i % 2 === 1 ? 'border-l-2 border-dashed border-night/15' : ''} ${
        i >= 2 ? 'border-t-2 border-dashed border-night/15 md:border-t-0' : ''
    } ${i === 2 ? 'md:border-l-2' : ''}`;

export function WelcomeSection({ intro, sightingsCount }: { intro: string; sightingsCount: number }) {
    return (
        <Section tone="light" wave labelledBy="boas-vindas" innerClassName="text-center">
            <Reveal>
                <Eyebrow>{t.welcome.eyebrow}</Eyebrow>
                <Display as="h1" id="boas-vindas" className="mt-3 -mr-[0.04em]">
                    {t.brand.name}
                </Display>
                <p className="mt-4 text-[clamp(1.05rem,0.95rem+0.5vw,1.35rem)] font-semibold text-horizon">
                    {t.brand.tagline}
                </p>
                <p className="mx-auto mt-8 max-w-[62ch] text-[1.075rem] leading-relaxed text-night/80">{intro}</p>
            </Reveal>

            <Reveal className="mx-auto mt-14 max-w-5xl">
                <dl className="grid grid-cols-2 rounded-[22px] bg-night/[0.035] text-left ring-1 ring-night/10 md:grid-cols-4">
                    <InfoCard
                        className={cell(0)}
                        icon={<PinIcon size="0.95rem" />}
                        label={t.welcome.where}
                        value={t.welcome.whereValue}
                        href="/o-lugar"
                    />
                    <InfoCard
                        className={cell(1)}
                        icon={<CompassIcon size="0.95rem" />}
                        label={t.welcome.city}
                        value={t.welcome.cityValue}
                    />
                    <InfoCard
                        className={cell(2)}
                        icon={<StarIcon size="0.95rem" />}
                        label={t.welcome.sightings}
                        value={
                            <span>
                                <CountUp value={sightingsCount} className="font-display text-xl font-extrabold" />{' '}
                                {t.welcome.sightingsValue(sightingsCount)}
                            </span>
                        }
                        href="/mapa"
                    />
                    <InfoCard
                        className={cell(3)}
                        icon={<BeamIcon size="0.95rem" />}
                        label={t.welcome.runway}
                        value={t.welcome.runwayValue}
                        extra={<Badge tone="car">{t.welcome.planning}</Badge>}
                    />
                </dl>
            </Reveal>
        </Section>
    );
}

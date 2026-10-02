import { motion, type Variants } from 'motion/react';
import { NightSkyArt, Polaroid } from '@/Components/Ui/Polaroid';
import { SightingPolaroid } from '@/Components/Sightings/SightingPolaroid';
import { Button } from '@/Components/Ui/Button';
import { Section } from '@/Components/Ui/Section';
import { Display, Eyebrow } from '@/Components/Ui/Typography';
import { usePrefersReducedMotion } from '@/hooks/usePrefersReducedMotion';
import { t } from '@/i18n/pt-BR';
import { spring, STAGGER } from '@/lib/motion';
import type { SightingCard } from '@/types';

const ROTATIONS = [-4, 3, -2, 4];
const OFFSETS = ['lg:translate-y-0', 'lg:translate-y-14', 'lg:translate-y-4', 'lg:translate-y-20'];

const table: Variants = { hidden: {}, shown: { transition: { staggerChildren: STAGGER } } };
const drop = (rotate: number): Variants => ({
    hidden: { opacity: 0, transform: `translateY(-36px) rotate(${rotate + 7}deg)` },
    shown: { opacity: 1, transform: `translateY(0px) rotate(0deg)`, transition: spring.paper },
});

/** Section 03. The latest approved reports, thrown on the table like instant photos. */
export function LogbookSection({ sightings }: { sightings: SightingCard[] }) {
    const reduced = usePrefersReducedMotion();
    const cards = sightings.length > 0 ? sightings.slice(0, 4) : null;

    return (
        <Section tone="dark" pattern="stars" wave labelledBy="livro" innerClassName="pb-[clamp(6rem,4rem+6vw,10rem)]!">
            <div className="flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between">
                <div className="min-w-0 lg:flex-1">
                    <Eyebrow tone="dark">{t.logbook.eyebrow}</Eyebrow>
                    <Display as="h2" id="livro" className="mt-3">
                        {t.logbook.title}
                    </Display>
                    <p className="mt-5 max-w-[48ch] text-lg text-moonlight/75">{t.logbook.lead}</p>
                </div>
                <div className="flex flex-wrap gap-3 lg:justify-end">
                    <Button href="/relatar">{t.logbook.report}</Button>
                    <Button href="/mapa" variant="secondary" tone="dark">
                        {t.logbook.map}
                    </Button>
                </div>
            </div>

            <motion.ul
                initial={reduced ? false : 'hidden'}
                whileInView="shown"
                viewport={{ once: true, margin: '0px 0px -15% 0px' }}
                variants={table}
                className="mt-16 grid grid-cols-2 gap-x-5 gap-y-10 sm:gap-x-8 lg:grid-cols-4 lg:gap-x-10"
            >
                {ROTATIONS.map((rotate, i) => {
                    const sighting = cards?.[i];
                    return (
                        <motion.li key={sighting?.id ?? `empty-${i}`} variants={drop(rotate)} className={OFFSETS[i]}>
                            {sighting ? (
                                <SightingPolaroid
                                    sighting={sighting}
                                    rotate={rotate}
                                    tape={i % 2 === 0 ? 'top' : 'corner'}
                                />
                            ) : (
                                <Polaroid
                                    href="/relatar"
                                    rotate={rotate}
                                    tape={i % 2 === 0 ? 'top' : 'corner'}
                                    art={<NightSkyArt seed={i + 3} />}
                                    caption={t.logbook.yourReport}
                                />
                            )}
                        </motion.li>
                    );
                })}
            </motion.ul>
        </Section>
    );
}

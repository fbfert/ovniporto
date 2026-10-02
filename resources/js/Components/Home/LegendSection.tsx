import { Button } from '@/Components/Ui/Button';
import { ConceptImage } from '@/Components/Ui/ConceptImage';
import { Polaroid } from '@/Components/Ui/Polaroid';
import { Reveal } from '@/Components/Ui/Reveal';
import { Section } from '@/Components/Ui/Section';
import { Badge, Display, Eyebrow } from '@/Components/Ui/Typography';
import { t } from '@/i18n/pt-BR';

/** Section 07. Asymmetric: the car on a big polaroid, the legend (not yet written) beside it. */
export function LegendSection({ teaser, pending }: { teaser: string; pending: boolean }) {
    return (
        <Section tone="light" labelledBy="lenda" wave>
            <div className="grid items-center gap-14 md:grid-cols-[minmax(0,1fr)_minmax(0,1.05fr)] lg:gap-24">
                <Reveal className="mx-auto w-full max-w-md md:mx-0">
                    <Polaroid
                        rotate={-3}
                        tape="corner"
                        art={
                            <ConceptImage
                                slug="yellow-car"
                                sizes="(min-width: 768px) 28rem, 90vw"
                                className="absolute inset-0"
                                badgeClassName="bottom-3 left-3"
                            />
                        }
                        imageClassName="aspect-[4/5]"
                        caption={pending ? t.legend.polaroid : 'O carro amarelo'}
                        href="/lenda"
                    />
                </Reveal>
                <Reveal>
                    <Eyebrow>{t.legend.eyebrow}</Eyebrow>
                    <Display as="h3" id="lenda" className="mt-3 max-w-[14ch]">
                        {t.legend.title}
                    </Display>
                    {pending && (
                        <Badge tone="neutral" className="mt-6">
                            {t.legend.pending}
                        </Badge>
                    )}
                    <p className="mt-6 max-w-[48ch] text-lg leading-relaxed text-night/80">{teaser}</p>
                    <div className="mt-8">
                        <Button href="/lenda" variant="ghost" iconRight={<span aria-hidden>→</span>}>
                            {t.legend.cta}
                        </Button>
                    </div>
                </Reveal>
            </div>
        </Section>
    );
}

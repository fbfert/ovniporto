import { YellowCarShape } from '@/Components/Scene/Art';
import { Button } from '@/Components/Ui/Button';
import { Polaroid } from '@/Components/Ui/Polaroid';
import { Reveal } from '@/Components/Ui/Reveal';
import { Section } from '@/Components/Ui/Section';
import { Badge, Display, Eyebrow } from '@/Components/Ui/Typography';
import { t } from '@/i18n/pt-BR';

function CarPortrait() {
    return (
        <svg viewBox="0 0 400 300" role="img" aria-label="Ilustração provisória do carro amarelo da lenda" className="h-full w-full">
            <rect width="400" height="300" fill="var(--color-night-blue)" />
            <path d="M150 0 L250 0 L320 300 L80 300 Z" fill="var(--color-beam)" opacity="0.16" />
            <path d="M185 0 L215 0 L250 300 L150 300 Z" fill="var(--color-beam-glow)" opacity="0.14" />
            <g transform="translate(200 190) rotate(-12) scale(1.45)">
                <YellowCarShape headlights />
            </g>
            <path d="M0 262 C120 250 280 250 400 262 L400 300 L0 300 Z" fill="var(--color-night)" />
        </svg>
    );
}

/** Section 07. Asymmetric: the car on a big polaroid, the legend (not yet written) beside it. */
export function LegendSection({ teaser, pending }: { teaser: string; pending: boolean }) {
    return (
        <Section tone="light" labelledBy="lenda" wave>
            <div className="grid items-center gap-14 md:grid-cols-[minmax(0,1fr)_minmax(0,1.05fr)] lg:gap-24">
                <Reveal className="mx-auto w-full max-w-md md:mx-0">
                    <Polaroid
                        rotate={-3}
                        tape="corner"
                        art={<CarPortrait />}
                        imageClassName="aspect-[4/3]"
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

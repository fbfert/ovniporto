import { Button } from '@/Components/Ui/Button';
import { Picture } from '@/Components/Ui/Picture';
import { Polaroid } from '@/Components/Ui/Polaroid';
import { Reveal } from '@/Components/Ui/Reveal';
import { Section } from '@/Components/Ui/Section';
import { Badge, Display, Eyebrow } from '@/Components/Ui/Typography';
import { t } from '@/i18n/pt-BR';

const copy = t.relatoSection;

/**
 * Section 08. The yellow car's relato opens on the home at night: its first paragraphs, the last one
 * (the moment the world goes quiet) set as a beat, and the Niva on a polaroid. Honest wait while unwritten.
 */
export function RelatoSection({ opening }: { opening: string[] | null }) {
    const lead = opening?.slice(0, -1) ?? [];
    const beat = opening?.at(-1);

    return (
        <Section tone="dark" pattern="stars" wave labelledBy="carro-amarelo">
            <div className="grid items-center gap-14 md:grid-cols-[minmax(0,1.1fr)_minmax(0,0.9fr)] lg:gap-24">
                <Reveal>
                    <Eyebrow tone="dark">{copy.eyebrow}</Eyebrow>
                    <Display as="h3" id="carro-amarelo" className="mt-3 max-w-[14ch]">
                        {copy.title}
                    </Display>
                    {opening ? (
                        <>
                            <div className="mt-8 max-w-[50ch] space-y-5 text-lg leading-relaxed text-moonlight/80">
                                {lead.map((paragraph) => (
                                    <p key={paragraph}>{paragraph}</p>
                                ))}
                            </div>
                            {beat && (
                                <p className="mt-6 max-w-[24ch] font-script text-[clamp(1.7rem,1.3rem+1.6vw,2.6rem)] leading-tight text-beam-glow">
                                    {beat}
                                </p>
                            )}
                            <div className="mt-10">
                                <Button href="/origem/relato" iconRight={<span aria-hidden>→</span>}>
                                    {copy.cta}
                                </Button>
                            </div>
                        </>
                    ) : (
                        <>
                            <p className="mt-6 max-w-[46ch] text-lg leading-relaxed text-moonlight/80">
                                {copy.pending}
                            </p>
                            <div className="mt-8">
                                <Button href="/origem" variant="ghost" tone="dark">
                                    {copy.pendingCta}
                                </Button>
                            </div>
                        </>
                    )}
                </Reveal>
                <Reveal className="mx-auto w-full max-w-md md:mx-0">
                    <Polaroid
                        rotate={3}
                        tape="top"
                        art={
                            <div className="absolute inset-0">
                                <Picture
                                    slug="niva-roadside"
                                    alt={t.concept.alt['niva-roadside']}
                                    sizes="(min-width: 768px) 28rem, 90vw"
                                    className="h-full w-full"
                                    imgClassName="h-full w-full object-cover object-[38%_50%]"
                                />
                                <Badge tone="horizon" className="absolute bottom-3 left-3">
                                    {t.concept.illustration}
                                </Badge>
                            </div>
                        }
                        imageClassName="aspect-[4/3]"
                        caption={copy.polaroid}
                        href={opening ? '/origem/relato' : undefined}
                    />
                </Reveal>
            </div>
        </Section>
    );
}

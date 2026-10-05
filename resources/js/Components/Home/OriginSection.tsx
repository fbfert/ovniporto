import { Button } from '@/Components/Ui/Button';
import { ManifestPicture, type ManifestEntry } from '@/Components/Ui/Picture';
import { Polaroid } from '@/Components/Ui/Polaroid';
import { Reveal } from '@/Components/Ui/Reveal';
import { Section } from '@/Components/Ui/Section';
import { Display, Eyebrow } from '@/Components/Ui/Typography';
import manifest from '@/data/origin-images.json';
import { t } from '@/i18n/pt-BR';
import type { ImageCredit } from '@/types/origin';

const copy = t.originSection;
const entries: Record<string, ManifestEntry | undefined> = manifest;

/** Section 07. Asymmetric: the stone star of Cachi on a big polaroid (credited), the origin beside it. */
export function OriginSection({ teaser, cover }: { teaser: string; cover: ImageCredit | null }) {
    const entry = cover ? entries[cover.slug] : undefined;

    return (
        <Section tone="light" labelledBy="origem" wave>
            <div className="grid items-center gap-14 md:grid-cols-[minmax(0,1fr)_minmax(0,1.05fr)] lg:gap-24">
                {cover && entry && (
                    <Reveal className="mx-auto w-full max-w-md md:mx-0">
                        <Polaroid
                            rotate={-3}
                            tape="corner"
                            art={
                                <ManifestPicture
                                    base="/origin"
                                    slug={cover.slug}
                                    entry={entry}
                                    alt={cover.alt}
                                    sizes="(min-width: 768px) 28rem, 90vw"
                                    className="absolute inset-0"
                                />
                            }
                            imageClassName="aspect-[4/5]"
                            caption={copy.polaroid}
                            href="/origem/cachi"
                        />
                        <p className="mt-6 text-[0.75rem] text-night/60">
                            {copy.photoCredit}: {cover.author}, {cover.license} ·{' '}
                            <a
                                href={cover.sourceUrl}
                                target="_blank"
                                rel="noopener noreferrer"
                                className="underline underline-offset-2 hover:text-horizon"
                            >
                                {copy.creditSource}
                            </a>
                        </p>
                    </Reveal>
                )}
                <Reveal>
                    <Eyebrow>{copy.eyebrow}</Eyebrow>
                    <Display as="h3" id="origem" className="mt-3 max-w-[14ch]">
                        {copy.title}
                    </Display>
                    <p className="mt-6 max-w-[48ch] text-lg leading-relaxed text-night/80">{teaser}</p>
                    <div className="mt-8">
                        <Button href="/origem" variant="ghost" iconRight={<span aria-hidden>→</span>}>
                            {copy.cta}
                        </Button>
                    </div>
                </Reveal>
            </div>
        </Section>
    );
}

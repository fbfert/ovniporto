import { Link } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { Prose } from '@/Components/Content/Prose';
import { WaitlistForm } from '@/Components/Home/WaitlistForm';
import { SeoHead } from '@/Components/Layout/SeoHead';
import { Button } from '@/Components/Ui/Button';
import type { ManifestEntry } from '@/Components/Ui/Picture';
import { Badge, Display, Eyebrow } from '@/Components/Ui/Typography';
import manifest from '@/data/concept.json';
import { t } from '@/i18n/pt-BR';
import { PublicLayout } from '@/Layouts/PublicLayout';

const copy = t.origin.relatoPage;

/**
 * The short paragraphs ("Nada.", "Altas.", "Magras.") arrive marked as beats by the server and are
 * set in the display face: the reader feels the pauses the way Julean tells them.
 */
const READING =
    'mx-auto text-[1.15rem] leading-[1.85] text-moonlight/90 sm:text-[1.2rem] [&_.relato-beat]:my-10 [&_.relato-beat]:font-display [&_.relato-beat]:text-[clamp(1.25rem,0.95rem+1.2vw,1.9rem)] [&_.relato-beat]:leading-tight [&_.relato-beat]:font-extrabold [&_.relato-beat]:tracking-[0.04em] [&_.relato-beat]:text-beam-glow [&_.relato-beat]:uppercase';

const scenes: Record<string, ManifestEntry | undefined> = manifest;

function srcSet(slug: string, format: 'avif' | 'webp'): string {
    return (scenes[slug]?.widths ?? []).map((w) => `/concept/${slug}-${w}.${format} ${w}w`).join(', ');
}

/**
 * The night of the relato stays behind the whole page, fixed, so the text scrolls over the scene.
 * Phones get the tall picture (beam above, Niva below); wide screens the landscape one, where both
 * fit across. The shade deepens where the reading starts.
 */
function Backdrop() {
    const wide = '(min-width: 768px)';
    return (
        <div aria-hidden className="pointer-events-none fixed inset-0 -z-10 bg-night">
            <picture>
                <source media={wide} type="image/avif" srcSet={srcSet('niva-roadside', 'avif')} sizes="100vw" />
                <source media={wide} type="image/webp" srcSet={srcSet('niva-roadside', 'webp')} sizes="100vw" />
                <source type="image/avif" srcSet={srcSet('niva-roadside-tall', 'avif')} sizes="100vw" />
                <source type="image/webp" srcSet={srcSet('niva-roadside-tall', 'webp')} sizes="100vw" />
                <img
                    src="/concept/niva-roadside-tall.jpg"
                    alt=""
                    fetchPriority="high"
                    className="h-full w-full object-cover object-[50%_62%] saturate-[0.9] md:object-[40%_60%]"
                />
            </picture>
            <div className="absolute inset-0 bg-[linear-gradient(180deg,rgb(6_17_33/0.25)_0%,rgb(6_17_33/0.45)_55%,rgb(6_17_33/0.8)_100%)] md:bg-[linear-gradient(90deg,rgb(6_17_33/0.1)_0%,rgb(6_17_33/0.35)_45%,rgb(6_17_33/0.8)_100%)]" />
        </div>
    );
}

function Cover() {
    return (
        <section data-tone="dark" className="relative flex min-h-[92svh] items-end">
            <div className="mx-auto w-full max-w-6xl px-5 pt-36 pb-16 sm:px-8 md:flex md:flex-col md:items-end md:text-right lg:pb-20">
                <Eyebrow tone="dark">{copy.eyebrow}</Eyebrow>
                <Display
                    as="h1"
                    className="mt-4 max-w-[16ch] text-[clamp(2.1rem,1rem+5vw,5rem)]! drop-shadow-[0_2px_18px_rgb(6_17_33/0.8)]"
                >
                    {copy.title}
                </Display>
                <p className="mt-6 max-w-[40ch] text-lg leading-relaxed text-moonlight/90 drop-shadow-[0_1px_8px_rgb(6_17_33/0.9)]">
                    {copy.lead}
                </p>
                <p className="mt-8 inline-block max-w-[46ch] rounded-[14px] border-2 border-dashed border-beam-glow/60 bg-night/60 px-4 py-3 text-[0.95rem] leading-snug text-beam-glow">
                    {copy.framing}
                </p>
            </div>
            <Badge tone="horizon" className="absolute top-[5.5rem] right-4 sm:right-6">
                {t.relatoSection.illustration}
            </Badge>
        </section>
    );
}

export default function Relato({ relatoHtml }: { relatoHtml: string | null }) {
    return (
        <div className="relative isolate text-moonlight">
            <SeoHead />
            <Backdrop />
            <Cover />

            <section data-tone="dark" aria-labelledby="relato" className="px-3 pb-24 sm:px-6">
                <h2 id="relato" className="sr-only">
                    {copy.title}
                </h2>
                <div className="mx-auto max-w-[46rem] rounded-[28px] bg-night/85 px-5 py-12 ring-1 ring-moonlight/10 sm:px-12 sm:py-16">
                    {relatoHtml ? (
                        <article>
                            <Prose html={relatoHtml} className={READING} />
                            <p className="mx-auto mt-14 max-w-[38rem] text-right font-script text-3xl text-beam-glow">
                                {copy.signature}
                            </p>
                        </article>
                    ) : (
                        <div className="mx-auto max-w-xl">
                            <p className="text-lg leading-relaxed text-moonlight/85">{t.origin.hub.relatoPending}</p>
                            <p className="mt-8 mb-3 font-script text-2xl text-beam-glow">{t.origin.hub.notify}</p>
                            <WaitlistForm source="origem" />
                        </div>
                    )}
                </div>
            </section>

            <section data-tone="dark" aria-labelledby="relato-fim" className="bg-night px-5 py-20 sm:px-8 sm:py-24">
                <div className="mx-auto flex max-w-3xl flex-col items-center gap-5 text-center">
                    <p
                        id="relato-fim"
                        className="font-script text-[clamp(1.8rem,1.3rem+1.8vw,2.8rem)] leading-tight text-beam-glow"
                    >
                        {copy.endTitle}
                    </p>
                    <p className="max-w-[48ch] leading-relaxed text-moonlight/75">{copy.endBody}</p>
                    <div className="mt-4 flex flex-wrap items-center justify-center gap-5">
                        <Button href="/relatar" size="lg">
                            {copy.report}
                        </Button>
                        <Button href="/origem/cachi" variant="ghost" tone="dark">
                            {copy.toCachi}
                        </Button>
                    </div>
                    <Link
                        href="/origem"
                        className="inline-flex min-h-11 items-center px-3 font-semibold text-moonlight/80 underline underline-offset-4 hover:text-beam-glow"
                    >
                        {copy.toOrigin}
                    </Link>
                </div>
            </section>
        </div>
    );
}

Relato.layout = (page: ReactNode) => <PublicLayout>{page}</PublicLayout>;

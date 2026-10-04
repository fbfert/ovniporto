import { Link } from '@inertiajs/react';
import { motion } from 'motion/react';
import type { ReactNode } from 'react';
import { ContentImage, type PhotoSources } from '@/Components/Ui/Picture';
import { spring } from '@/lib/motion';

/**
 * Instant photo on the table: white-ish frame with a deeper bottom edge, a strip
 * of tape and a slight tilt. Hover straightens it (pointer devices only: Motion
 * ignores touch for hover gestures).
 */
export function Polaroid({
    src,
    sources,
    alt = '',
    art,
    caption,
    rotate = -3,
    tape = 'top',
    href,
    className = '',
    imageClassName = 'aspect-[4/5]',
}: {
    src?: string | null;
    sources?: PhotoSources | null;
    alt?: string;
    art?: ReactNode;
    caption: ReactNode;
    rotate?: number;
    tape?: 'top' | 'corner' | 'none';
    href?: string;
    className?: string;
    imageClassName?: string;
}) {
    const body = (
        <motion.figure
            initial={false}
            style={{ rotate }}
            whileHover={{ rotate: 0, y: -6, scale: 1.015 }}
            transition={spring.soft}
            className={`relative bg-moonlight p-3 pb-0 text-night shadow-polaroid ${className}`}
        >
            {tape === 'top' && (
                <span aria-hidden className="absolute -top-3 left-1/2 z-10 h-7 w-24 -translate-x-1/2 -rotate-3 tape" />
            )}
            {tape === 'corner' && (
                <span aria-hidden className="absolute -top-2 -right-5 z-10 h-6 w-20 rotate-[38deg] tape" />
            )}
            <div className={`relative overflow-hidden bg-night ${imageClassName}`}>
                {src ? (
                    <ContentImage
                        src={src}
                        alt={alt}
                        sources={sources}
                        sizes="(min-width: 1024px) 25vw, (min-width: 640px) 50vw, 100vw"
                        className="h-full w-full"
                    />
                ) : (
                    art
                )}
                <span
                    aria-hidden
                    className="pointer-events-none absolute inset-0 shadow-[inset_0_0_0_1px_rgb(6_17_33/0.25)]"
                />
            </div>
            <figcaption className="flex min-h-16 items-center justify-center px-1 py-3 text-center font-script text-[1.35rem] leading-tight">
                {caption}
            </figcaption>
        </motion.figure>
    );

    if (!href) return body;
    // A link around a <figure> gets no name in Chromium: say what the card is.
    const name = [alt, typeof caption === 'string' ? caption : null].filter(Boolean).join('. ') || undefined;
    return (
        <Link href={href} aria-label={name} className="block rounded-sm focus-visible:outline-offset-8">
            {body}
        </Link>
    );
}

/** A photo-less "sky": night-blue gradient, three points of light and the report type. */
export function NightSkyArt({ label, seed = 1 }: { label?: string; seed?: number }) {
    const rand = (n: number) => (Math.sin(seed * 91.7 + n * 13.3) * 0.5 + 0.5) * 100;
    const lights = [0, 1, 2].map((i) => ({ x: 15 + rand(i) * 0.7, y: 12 + rand(i + 5) * 0.55, big: i === 0 }));
    return (
        <div className="absolute inset-0 bg-[radial-gradient(90%_70%_at_30%_10%,var(--color-night-blue),var(--color-night))]">
            {lights.map((l, i) => (
                <span
                    key={i}
                    className={`absolute rounded-full bg-beam-glow ${l.big ? 'size-2.5 shadow-[0_0_18px_4px_rgb(84_201_51/0.55)]' : 'size-1.5 shadow-[0_0_10px_2px_rgb(173_219_161/0.45)]'}`}
                    style={{ left: `${l.x}%`, top: `${l.y}%` }}
                />
            ))}
            <span className="absolute inset-x-0 bottom-0 h-1/4 bg-[linear-gradient(to_top,rgb(73_67_131/0.5),transparent)]" />
            {label && (
                <span className="absolute bottom-3 left-3 font-script text-2xl leading-none text-moonlight/90">
                    {label}
                </span>
            )}
        </div>
    );
}

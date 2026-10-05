import type { ElementType, ReactNode } from 'react';

type Tone = 'light' | 'dark';

/** Handwritten overline in Caveat, tilted like it was scribbled on the poster. */
export function Eyebrow({
    children,
    tone = 'light',
    className = '',
    as: Tag = 'p',
}: {
    children: ReactNode;
    tone?: Tone;
    className?: string;
    as?: ElementType;
}) {
    return (
        <Tag
            className={`-rotate-2 font-script text-[clamp(1.5rem,1.2rem+1.2vw,2.1rem)] leading-[1.15] font-semibold ${
                tone === 'dark' ? 'text-beam-glow' : 'text-horizon'
            } ${className}`}
        >
            {children}
        </Tag>
    );
}

const displaySizes = {
    h1: 'text-[clamp(2rem,9.4vw,9rem)] leading-[1.02]',
    h2: 'text-[clamp(1.6rem,0.6rem+4.4vw,4.6rem)] leading-[1.04]',
    h3: 'text-[clamp(1.6rem,1rem+3vw,3.6rem)] leading-[1.06]',
} as const;

/** Wide geometric display type, uppercase, as on the printed sticker. */
export function Display({
    as = 'h2',
    children,
    outlined = false,
    className = '',
    id,
}: {
    as?: 'h1' | 'h2' | 'h3' | 'p' | 'span';
    children: ReactNode;
    outlined?: boolean;
    className?: string;
    id?: string;
}) {
    const Tag = as;
    const size = as === 'h1' || as === 'h2' || as === 'h3' ? displaySizes[as] : '';
    return (
        <Tag
            id={id}
            className={`font-display font-extrabold tracking-[0.04em] uppercase ${size} ${
                outlined ? 'text-outline' : ''
            } ${className}`}
        >
            {children}
        </Tag>
    );
}

type BadgeTone = 'beam' | 'car' | 'horizon' | 'neutral';

const badgeTones: Record<BadgeTone, string> = {
    beam: 'bg-beam/15 text-night ring-beam/50 [[data-tone=dark]_&]:text-beam-glow',
    car: 'bg-car text-night ring-night/10',
    horizon: 'bg-horizon text-moonlight ring-moonlight/20',
    neutral:
        'bg-night/6 text-night/75 ring-night/15 [[data-tone=dark]_&]:bg-moonlight/10 [[data-tone=dark]_&]:text-moonlight/80 [[data-tone=dark]_&]:ring-moonlight/20',
};

export function Badge({
    children,
    tone = 'neutral',
    className = '',
}: {
    children: ReactNode;
    tone?: BadgeTone;
    className?: string;
}) {
    return (
        <span
            className={`inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[0.72rem] leading-none font-semibold ring-1 ring-inset ${badgeTones[tone]} ${className}`}
        >
            {children}
        </span>
    );
}

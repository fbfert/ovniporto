import type { ReactNode } from 'react';
import { Starfield } from '@/Components/Scene/Starfield';

type Tone = 'light' | 'dark' | 'dusk';

const tones: Record<Tone, string> = {
    light: 'bg-moonlight text-night',
    dusk: 'bg-[color-mix(in_srgb,var(--color-night)_4%,var(--color-moonlight))] text-night',
    dark: 'bg-night text-moonlight grain',
};

const waveFill: Record<Tone, string> = {
    light: 'text-moonlight',
    dusk: 'text-[color-mix(in_srgb,var(--color-night)_4%,var(--color-moonlight))]',
    dark: 'text-night',
};

/**
 * A page band. Dark bands get the starfield and report themselves to the header
 * (data-tone) so the floating menu can invert. `wave` draws a soft hill-line edge
 * over the previous band instead of a straight cut: the serra skyline, simplified.
 * `backdrop` paints behind the content (e.g. a faded illustration).
 */
export function Section({
    tone = 'light',
    pattern = 'none',
    wave = false,
    id,
    labelledBy,
    className = '',
    innerClassName = '',
    backdrop,
    children,
}: {
    tone?: Tone;
    pattern?: 'none' | 'stars';
    wave?: boolean;
    id?: string;
    labelledBy?: string;
    className?: string;
    innerClassName?: string;
    backdrop?: ReactNode;
    children: ReactNode;
}) {
    const dark = tone === 'dark';
    return (
        <section
            id={id}
            aria-labelledby={labelledBy}
            data-tone={dark ? 'dark' : 'light'}
            className={`relative ${tones[tone]} ${className}`}
        >
            {wave && (
                <svg
                    aria-hidden
                    viewBox="0 0 1440 56"
                    preserveAspectRatio="none"
                    className={`pointer-events-none absolute inset-x-0 -top-[38px] z-[2] h-10 w-full ${waveFill[tone]}`}
                >
                    <path
                        fill="currentColor"
                        d="M0 56V34c96-14 168-26 262-16 92 10 150 26 252 22 108-4 170-34 284-34 112 0 168 30 278 30 104 0 168-24 260-26 46-1 80 4 104 8v38H0Z"
                    />
                </svg>
            )}
            {backdrop && <div className="absolute inset-0 z-[1] overflow-hidden">{backdrop}</div>}
            {dark && pattern === 'stars' && <Starfield className="absolute inset-0 z-0" density="low" parallax />}
            <div
                className={`relative z-[2] mx-auto w-full max-w-[84rem] px-5 py-[clamp(4rem,3rem+5vw,8rem)] sm:px-8 ${innerClassName}`}
            >
                {children}
            </div>
        </section>
    );
}

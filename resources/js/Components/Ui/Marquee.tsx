import { useId, useState } from 'react';
import { t } from '@/i18n/pt-BR';

const FLAG_COLORS = ['var(--color-car)', 'var(--color-beam)', 'var(--color-moonlight)', 'var(--color-beam-glow)'];

/** Festa-junina bunting hanging from a string: four alternating brand colors. */
function Bunting() {
    const id = `bunting-${useId().replace(/:/g, '')}`;
    return (
        <svg aria-hidden className="absolute inset-x-0 -top-[18px] h-7 w-full" preserveAspectRatio="none">
            <defs>
                <pattern id={id} width="152" height="28" patternUnits="userSpaceOnUse">
                    <path
                        d="M0 3 Q76 9 152 3"
                        stroke="var(--color-night)"
                        strokeOpacity="0.55"
                        strokeWidth="1.2"
                        fill="none"
                    />
                    {FLAG_COLORS.map((color, i) => (
                        <path key={i} d={`M${6 + i * 38} 4 L${32 + i * 38} 4.5 L${19 + i * 38} 26 Z`} fill={color} />
                    ))}
                </pattern>
            </defs>
            <rect width="100%" height="28" fill={`url(#${id})`} />
        </svg>
    );
}

function Saucer() {
    return (
        <svg aria-hidden viewBox="0 0 40 20" className="mx-6 h-5 w-10 shrink-0 text-beam-glow">
            <path d="M12 9 C13 2 27 2 28 9 Z" fill="currentColor" opacity="0.85" />
            <ellipse cx="20" cy="11" rx="19" ry="5" fill="currentColor" />
            <circle cx="9" cy="12" r="1.3" fill="var(--color-horizon)" />
            <circle cx="20" cy="13.5" r="1.3" fill="var(--color-horizon)" />
            <circle cx="31" cy="12" r="1.3" fill="var(--color-horizon)" />
        </svg>
    );
}

/**
 * Endless strip, slightly tilted and wider than the screen so its ends never
 * show. Pauses on hover and with its own button (motion over 5 s must be stoppable:
 * WCAG 2.2.2); becomes a static centered line with reduced motion.
 */
export function Marquee({
    items,
    speed = 'normal',
    tilt = -1.5,
}: {
    items: readonly string[];
    speed?: 'slow' | 'normal';
    tilt?: number;
}) {
    const [paused, setPaused] = useState(false);
    const run = (
        <span className="flex shrink-0 items-center">
            {items.map((item) => (
                <span key={item} className="flex items-center">
                    <span className="font-display text-[clamp(1rem,0.8rem+1vw,1.5rem)] font-bold tracking-[0.06em] whitespace-nowrap uppercase">
                        {item}
                    </span>
                    <Saucer />
                </span>
            ))}
        </span>
    );

    return (
        <div className="relative z-[3] -my-3 overflow-x-clip py-8" aria-label={items.join(' · ')} role="marquee">
            <div
                className="group/marquee relative -mx-[5vw] bg-horizon py-4 text-moonlight shadow-[0_20px_40px_-24px_rgb(6_17_33/0.6)]"
                style={{ transform: `rotate(${tilt}deg)` }}
            >
                <Bunting />
                <div
                    aria-hidden
                    className={`flex w-max animate-marquee group-hover/marquee:[animation-play-state:paused] motion-reduce:hidden ${
                        paused ? '[animation-play-state:paused]' : ''
                    }`}
                    style={{ ['--marquee-duration' as string]: speed === 'slow' ? '60s' : '38s' }}
                >
                    {run}
                    {run}
                    {run}
                    {run}
                </div>
                <p className="hidden px-6 text-center font-display font-bold tracking-[0.06em] uppercase motion-reduce:block">
                    {items.join(' ✦ ')}
                </p>
                <button
                    type="button"
                    aria-pressed={paused}
                    onClick={() => setPaused((value) => !value)}
                    className="absolute top-1/2 right-[calc(5vw+0.5rem)] flex size-11 -translate-y-1/2 items-center justify-center rounded-full bg-night/70 text-moonlight hover:bg-night motion-reduce:hidden"
                >
                    <span className="sr-only">{t.marquee.pause}</span>
                    <svg aria-hidden viewBox="0 0 24 24" className="size-5" fill="currentColor">
                        {paused ? <path d="M8 5.5v13l11-6.5z" /> : <path d="M7 5h3.5v14H7zM13.5 5H17v14h-3.5z" />}
                    </svg>
                </button>
            </div>
        </div>
    );
}

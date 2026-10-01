import { useId } from 'react';
import { SaucerShape, YellowCarShape } from '@/Components/Scene/Art';

type Size = 'sm' | 'md' | 'lg';

const sizes: Record<Size, string> = {
    sm: 'size-12',
    md: 'size-40',
    lg: 'size-[clamp(13rem,9rem+18vw,22rem)]',
};

/**
 * Provisional seal (the printed sticker), drawn in code until the real artwork
 * lands in public/brand/seal.svg. Text sits on circular paths in the brand fonts.
 */
export function SealArt({ title = 'Selo OVNIPORTO · Lages SC', simplified = false }: { title?: string; simplified?: boolean }) {
    const uid = useId().replace(/:/g, '');
    const top = `seal-top-${uid}`;
    const bottom = `seal-bottom-${uid}`;
    const sky = `seal-sky-${uid}`;
    const beam = `seal-beam-${uid}`;
    const clip = `seal-clip-${uid}`;

    return (
        <svg viewBox="0 0 240 240" role="img" aria-label={title} className="h-full w-full">
            <defs>
                <path id={top} d="M 30 120 A 90 90 0 0 1 210 120" />
                <path id={bottom} d="M 22 120 A 98 98 0 0 0 218 120" />
                <clipPath id={clip}>
                    <circle cx="120" cy="120" r="65.5" />
                </clipPath>
                <radialGradient id={sky} cx="50%" cy="20%" r="80%">
                    <stop offset="0%" stopColor="var(--color-night-blue)" />
                    <stop offset="100%" stopColor="var(--color-night)" />
                </radialGradient>
                <linearGradient id={beam} x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" stopColor="var(--color-beam)" stopOpacity="0.85" />
                    <stop offset="100%" stopColor="var(--color-beam)" stopOpacity="0.1" />
                </linearGradient>
            </defs>
            <circle cx="120" cy="120" r="119" fill="var(--color-night)" />
            <circle cx="120" cy="120" r="113" fill="none" stroke="var(--color-beam)" strokeWidth="3.5" />
            <circle cx="120" cy="120" r="66" fill={`url(#${sky})`} stroke="var(--color-moonlight)" strokeOpacity="0.25" />

            {!simplified && (
                <>
                    <text
                        fill="var(--color-moonlight)"
                        fontFamily="var(--font-display)"
                        fontWeight="800"
                        fontSize="25"
                        letterSpacing="2.4"
                    >
                        <textPath href={`#${top}`} startOffset="50%" textAnchor="middle">
                            OVNIPORTO
                        </textPath>
                    </text>
                    <text
                        fill="var(--color-beam-glow)"
                        fontFamily="var(--font-sans)"
                        fontWeight="600"
                        fontSize="11.5"
                        letterSpacing="3.2"
                    >
                        <textPath href={`#${bottom}`} startOffset="50%" textAnchor="middle" dominantBaseline="hanging">
                            LAGES · SANTA CATARINA
                        </textPath>
                    </text>
                    <path d="M22 120 l3 -6 3 6 -3 6 Z M212 120 l3 -6 3 6 -3 6 Z" fill="var(--color-car)" />
                </>
            )}

            <g clipPath={`url(#${clip})`}>
            {/* tiny stars */}
            {[
                [88, 82, 1.2],
                [150, 76, 1],
                [160, 100, 1.4],
                [80, 108, 0.9],
                [128, 68, 0.8],
            ].map(([x, y, r]) => (
                <circle key={`${x}-${y}`} cx={x} cy={y} r={r} fill="var(--color-moonlight)" opacity="0.8" />
            ))}

            {/* beam from saucer to car */}
            <path d="M110 100 L130 100 L146 156 L94 156 Z" fill={`url(#${beam})`} />
            <g transform="translate(120 96) scale(0.24)">
                <SaucerShape />
            </g>
            <path d="M58 158 C90 152 150 152 182 158 L182 186 L58 186 Z" fill="var(--color-night)" />
            <g transform="translate(120 160) scale(0.24)">
                <YellowCarShape />
            </g>
            </g>
        </svg>
    );
}

export function Seal({ size = 'md', glow = false, className = '' }: { size?: Size; glow?: boolean; className?: string }) {
    return (
        <span className={`relative inline-block ${sizes[size]} ${className}`}>
            {glow && (
                <span
                    aria-hidden
                    className="absolute -inset-[9%] animate-seal-glow rounded-full bg-[radial-gradient(closest-side,rgb(84_201_51/0.55),rgb(84_201_51/0.18)_55%,transparent_72%)] motion-reduce:animate-none"
                />
            )}
            <span className="relative block h-full w-full rounded-full shadow-[0_18px_50px_-18px_rgb(6_17_33/0.8)]">
                <SealArt simplified={size === 'sm'} />
            </span>
        </span>
    );
}

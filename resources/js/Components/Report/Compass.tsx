import { GAZE } from './draft';

/** Compass rose whose needle turns to the chosen direction (spring-free CSS rotation, 400ms). */
export function Compass({ direction }: { direction: string | null }) {
    const index = direction ? GAZE.indexOf(direction as (typeof GAZE)[number]) : -1;
    const angle = index < 0 ? 0 : index * 45;

    return (
        <svg viewBox="0 0 120 120" className="size-28 shrink-0" aria-hidden>
            <circle cx="60" cy="60" r="56" fill="none" stroke="currentColor" strokeOpacity=".2" strokeWidth="2" />
            <circle
                cx="60"
                cy="60"
                r="44"
                fill="none"
                stroke="currentColor"
                strokeOpacity=".12"
                strokeDasharray="3 5"
            />
            {GAZE.map((label, i) => {
                const rad = ((i * 45 - 90) * Math.PI) / 180;
                return (
                    <text
                        key={label}
                        x={60 + Math.cos(rad) * 50}
                        y={60 + Math.sin(rad) * 50 + 3.5}
                        textAnchor="middle"
                        fontSize={label.length > 1 ? 7 : 9}
                        fontWeight={label === direction ? 800 : 600}
                        fill="currentColor"
                        opacity={label === direction ? 1 : 0.55}
                    >
                        {label}
                    </text>
                );
            })}
            <g
                style={{
                    transform: `rotate(${angle}deg)`,
                    transformOrigin: '60px 60px',
                    transition: 'transform 400ms var(--ease-snap)',
                    opacity: index < 0 ? 0.35 : 1,
                }}
            >
                <path d="M60 18 L67 60 L60 56 L53 60 Z" fill="var(--color-beam)" />
                <path d="M60 102 L67 60 L60 64 L53 60 Z" fill="currentColor" opacity=".35" />
            </g>
            <circle cx="60" cy="60" r="4" fill="currentColor" />
        </svg>
    );
}

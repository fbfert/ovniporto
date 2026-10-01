import { AraucariaShape, SaucerShape, YellowCarShape } from '@/Components/Scene/Art';

/**
 * Placeholder "concept" of the finished runway, composed from the scene
 * primitives: stone stars on the ground, the vigil benches' red lamps, the
 * suspended yellow car. Always shown with the "conceito" badge.
 */
export function RunwayConceptArt() {
    const stones = [
        { x: 300, y: 330, r: 26 },
        { x: 420, y: 352, r: 34 },
        { x: 560, y: 340, r: 22 },
        { x: 190, y: 360, r: 18 },
        { x: 660, y: 362, r: 28 },
    ];
    return (
        <svg viewBox="0 0 800 450" role="img" aria-label="Ilustração conceitual da pista de pouso de pedra à noite" className="h-full w-full">
            <defs>
                <radialGradient id="concept-sky" cx="50%" cy="0%" r="100%">
                    <stop offset="0%" stopColor="var(--color-night-blue)" />
                    <stop offset="100%" stopColor="var(--color-night)" />
                </radialGradient>
                <linearGradient id="concept-haze" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" stopColor="var(--color-horizon)" stopOpacity="0" />
                    <stop offset="100%" stopColor="var(--color-horizon)" stopOpacity="0.55" />
                </linearGradient>
            </defs>
            <rect width="800" height="450" fill="url(#concept-sky)" />
            {Array.from({ length: 40 }, (_, i) => (
                <circle
                    key={i}
                    cx={(i * 197) % 800}
                    cy={(i * 89) % 240}
                    r={i % 5 === 0 ? 1.6 : 0.9}
                    fill="var(--color-moonlight)"
                    opacity={0.35 + (i % 4) * 0.15}
                />
            ))}
            <rect y="150" width="800" height="160" fill="url(#concept-haze)" />
            <path d="M0 300 C140 270 260 296 400 290 C540 284 660 262 800 276 L800 450 L0 450 Z" fill="var(--color-night-blue)" />
            <g transform="translate(110 296)">
                <AraucariaShape height={150} seed={4} />
            </g>
            <g transform="translate(720 284)">
                <AraucariaShape height={120} seed={9} />
            </g>
            <path d="M0 330 C200 316 600 316 800 330 L800 450 L0 450 Z" fill="var(--color-night)" />
            {stones.map(({ x, y, r }) => (
                <path
                    key={x}
                    transform={`translate(${x} ${y}) scale(1 0.35)`}
                    d={`M0 ${-r} L${r * 0.28} ${-r * 0.3} L${r} 0 L${r * 0.28} ${r * 0.3} L0 ${r} L${-r * 0.28} ${r * 0.3} L${-r} 0 L${-r * 0.28} ${-r * 0.3} Z`}
                    fill="var(--color-moonlight)"
                    opacity="0.75"
                />
            ))}
            {[150, 250, 520, 640].map((x) => (
                <circle key={x} cx={x} cy={386} r={3} fill="var(--color-car)" opacity="0.9" />
            ))}
            <path d="M560 140 L586 140 L612 236 L534 236 Z" fill="var(--color-beam)" opacity="0.18" />
            <g transform="translate(573 136) scale(0.3)">
                <SaucerShape />
            </g>
            <g transform="translate(573 236) rotate(-8) scale(0.42)">
                <YellowCarShape />
            </g>
        </svg>
    );
}

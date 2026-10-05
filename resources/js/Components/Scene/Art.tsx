/**
 * Hand-drawn SVG primitives for the OVNIPORTO night scene. Every shape is drawn
 * around its own origin so it can be placed and animated inside any <svg>.
 * Colors are brand tokens only.
 */

import { useId } from 'react';

const C = {
    moonlight: 'var(--color-moonlight)',
    night: 'var(--color-night)',
    nightBlue: 'var(--color-night-blue)',
    horizon: 'var(--color-horizon)',
    beam: 'var(--color-beam)',
    beamGlow: 'var(--color-beam-glow)',
    car: 'var(--color-car)',
} as const;

/**
 * The yellow car of the legend: a 3-door Lada Niva (VAZ-2121), side view, facing right.
 * Drawn to the real proportions (3.72 m long, 1.64 m tall, 2.20 m wheelbase, 1 unit ≈ 26.6 mm):
 * wheels close to the corners, short flat hood, blunt vertical nose, upright windshield,
 * tall glasshouse with a long door and vent pane, wide C-pillar, slab sides with one crease,
 * small steel wheels and a lot of ground clearance. Origin = ground, center. Width ≈ 140.
 */
export function YellowCarShape({ headlights = false }: { headlights?: boolean }) {
    const wheels = [-41.5, 41.5];
    const beam = useId();
    return (
        <g>
            {headlights && (
                <>
                    <defs>
                        <linearGradient id={beam} x1="0" x2="1" y1="0" y2="0">
                            <stop offset="0" stopColor={C.car} stopOpacity={0.55} />
                            <stop offset="1" stopColor={C.car} stopOpacity={0} />
                        </linearGradient>
                    </defs>
                    <path d="M70 -33.5 L128 -46 L128 -16 Z" fill={`url(#${beam})`} />
                </>
            )}
            {/* shadow of the underbody, seen through the high clearance */}
            <rect x={-56} y={-12} width={112} height={4} rx={2} fill={C.night} opacity={0.6} />
            {/* body: vertical tailgate, flat roof, upright windshield, short flat hood, blunt nose */}
            <path
                d="M-68 -12 L-68 -56 Q-68 -61.5 -62.5 -61.5 L10 -61.5 Q12.5 -61.5 13.6 -59.4 L24.5 -40.5
                   L67.5 -37.6 Q70 -37.4 70 -35 L70 -12
                   L57 -12 A15.5 15.5 0 0 0 26 -12 L-26 -12 A15.5 15.5 0 0 0 -57 -12 Z"
                fill={C.car}
            />
            {/* lower body below the crease, a shade darker: the slab-sided look */}
            <path
                d="M-68 -12 L-68 -27 L70 -27 L70 -12 L57 -12 A15.5 15.5 0 0 0 26 -12 L-26 -12 A15.5 15.5 0 0 0 -57 -12 Z"
                fill={C.night}
                opacity={0.07}
            />
            <path d="M-68 -27.5 L70 -27.5" stroke={C.moonlight} strokeOpacity={0.35} strokeWidth={0.9} />
            {/* rain gutter and hood shine */}
            <path d="M-62 -58.6 L9 -58.6" stroke={C.night} strokeOpacity={0.25} strokeWidth={0.9} />
            <path
                d="M-58 -60 L4 -60"
                stroke={C.moonlight}
                strokeOpacity={0.5}
                strokeWidth={1.6}
                strokeLinecap="round"
            />
            <path
                d="M30 -38.6 L62 -36.6"
                stroke={C.moonlight}
                strokeOpacity={0.4}
                strokeWidth={1.3}
                strokeLinecap="round"
            />
            {/* glasshouse: big rear side window with its rounded corner, wide C-pillar,
                long door window with the vent pane, thin pillars */}
            <path d="M-58 -41 L-58 -51 Q-58 -57 -52 -57 L-27 -57 L-27 -41 Z" fill={C.nightBlue} />
            <path d="M-23 -41 L-23 -57 L9.5 -57 L21 -41 Z" fill={C.nightBlue} />
            <path d="M8 -57 L12.5 -41" stroke={C.car} strokeWidth={1.4} />
            <path
                d="M-52 -45 L-45 -54"
                stroke={C.beamGlow}
                strokeOpacity={0.35}
                strokeWidth={1.8}
                strokeLinecap="round"
            />
            <path
                d="M-15 -45 L-8 -54"
                stroke={C.beamGlow}
                strokeOpacity={0.35}
                strokeWidth={1.8}
                strokeLinecap="round"
            />
            {/* the long door: shut lines, handle at the rear of the door, small mirror */}
            <path
                d="M-25 -41 L-25 -15.5 M24 -40 L24 -16"
                stroke={C.night}
                strokeOpacity={0.35}
                strokeWidth={1.1}
                fill="none"
            />
            <rect x={-21} y={-38.2} width={6.5} height={1.8} rx={0.9} fill={C.night} opacity={0.55} />
            <path d="M20.5 -44 L24.5 -44 L24.5 -40.5 L21.5 -40.5 Z" fill={C.night} opacity={0.75} />
            {/* fender repeater, tall tail light, headlight edge on the blunt nose */}
            <rect x={58} y={-33} width={3} height={1.6} rx={0.8} fill={C.night} opacity={0.45} />
            <rect x={-68} y={-40} width={2.2} height={9} rx={0.8} fill={C.night} opacity={0.6} />
            <rect x={68.8} y={-34.5} width={1.8} height={4.6} rx={0.9} fill={C.moonlight} />
            {/* thin dark bumpers, proud of the body */}
            <rect x={-70.5} y={-20} width={6} height={4} rx={1} fill={C.night} />
            <rect x={65.5} y={-21} width={7} height={4} rx={1} fill={C.night} />
            {/* round wheel arches with a lip, small 16" steel wheels */}
            {wheels.map((cx) => (
                <g key={cx}>
                    <path
                        d={`M${cx - 15.5} -12 A15.5 15.5 0 0 1 ${cx + 15.5} -12`}
                        stroke={C.night}
                        strokeOpacity={0.4}
                        strokeWidth={1.2}
                        fill="none"
                    />
                    <circle cx={cx} cy={-12.9} r={12.9} fill={C.night} />
                    <circle cx={cx} cy={-12.9} r={7.4} fill={C.moonlight} opacity={0.8} />
                    <circle
                        cx={cx}
                        cy={-12.9}
                        r={5.2}
                        fill="none"
                        stroke={C.night}
                        strokeOpacity={0.3}
                        strokeWidth={0.8}
                    />
                    <circle cx={cx} cy={-12.9} r={2.4} fill={C.night} opacity={0.75} />
                </g>
            ))}
        </g>
    );
}

/** The saucer. Origin = center of the rim. Width ≈ 220. */
export function SaucerShape({ lightsClassName = '' }: { lightsClassName?: string }) {
    const lights = [-84, -56, -28, 0, 28, 56, 84];
    return (
        <g>
            <ellipse cx={0} cy={16} rx={46} ry={9} fill={C.beam} opacity={0.55} />
            <path d="M-50 -6 C-46 -52 46 -52 50 -6 Z" fill={C.beamGlow} opacity={0.9} />
            <path
                d="M-36 -14 C-30 -40 4 -46 14 -40"
                stroke={C.moonlight}
                strokeOpacity={0.8}
                strokeWidth={3}
                fill="none"
                strokeLinecap="round"
            />
            <ellipse cx={0} cy={0} rx={110} ry={22} fill={C.nightBlue} />
            <ellipse cx={0} cy={-4} rx={110} ry={16} fill={C.moonlight} opacity={0.92} />
            <ellipse cx={0} cy={-6} rx={78} ry={9} fill={C.moonlight} />
            <path d="M-110 -2 C-60 12 60 12 110 -2" stroke={C.night} strokeOpacity={0.25} strokeWidth={2} fill="none" />
            {lights.map((x, i) => (
                <circle
                    key={x}
                    cx={x}
                    cy={8 - Math.abs(x) / 18}
                    r={4.2}
                    fill={C.beam}
                    className={lightsClassName}
                    style={{ animationDelay: `${i * 140}ms` }}
                />
            ))}
        </g>
    );
}

/**
 * Araucaria angustifolia silhouette: straight trunk and a flat, candelabra-shaped
 * crown whose branches curve upward to tufted tips. Origin = base of the trunk.
 */
export function AraucariaShape({ height, fill = C.night, seed = 1 }: { height: number; fill?: string; seed?: number }) {
    const h = height;
    const levels = 4;
    const branches: string[] = [];
    const tufts: Array<{ x: number; y: number; rx: number; ry: number }> = [];
    const jitter = (n: number) => Math.sin(seed * 12.9898 + n * 78.233) * 0.5 + 0.5;

    // Mature araucárias carry their crown on the top third: branches leave the
    // trunk, rise like a candelabrum and end in dense clumps that form a flat top.
    for (let i = 0; i < levels; i++) {
        const y = -h * (0.68 + i * 0.065);
        const span = h * (0.4 - i * 0.075) * (0.88 + jitter(i) * 0.24);
        const tipY = -h * (0.955 + jitter(i + 10) * 0.035);
        for (const dir of [-1, 1]) {
            const tipX = dir * span;
            branches.push(
                `M0 ${y.toFixed(1)} C${(dir * span * 0.55).toFixed(1)} ${(y + h * 0.01).toFixed(1)} ${(tipX * 0.96).toFixed(1)} ${(y - h * 0.03).toFixed(1)} ${tipX.toFixed(1)} ${tipY.toFixed(1)}`,
            );
            const size = h * (0.105 - i * 0.012) * (0.85 + jitter(i + 20) * 0.3);
            tufts.push({ x: tipX, y: tipY, rx: size, ry: size * 0.42 });
            tufts.push({ x: tipX * 0.78, y: tipY + size * 0.35, rx: size * 0.7, ry: size * 0.32 });
        }
    }

    const stroke = Math.max(1.6, h * 0.024);

    return (
        <g>
            <path
                d={`M-${h * 0.016} 0 L-${h * 0.009} ${-h * 0.97} L${h * 0.009} ${-h * 0.97} L${h * 0.016} 0 Z`}
                fill={fill}
            />
            <path d={branches.join(' ')} stroke={fill} strokeWidth={stroke} fill="none" strokeLinecap="round" />
            {tufts.map((t, i) => (
                <ellipse key={i} cx={t.x} cy={t.y} rx={t.rx} ry={t.ry} fill={fill} />
            ))}
            <ellipse cx={0} cy={-h * 0.975} rx={h * 0.1} ry={h * 0.04} fill={fill} />
        </g>
    );
}

export const SERRA = {
    far: 'M0 760 C160 700 300 732 440 712 C600 690 700 742 860 722 C1020 702 1140 668 1300 690 C1440 708 1520 690 1600 700 L1600 1000 L0 1000 Z',
    near: 'M0 822 C200 792 380 842 560 860 C680 871 740 873 800 873 C880 873 960 867 1080 851 C1260 827 1420 800 1600 812 L1600 1000 L0 1000 Z',
} as const;

/** Placement of trees on the ridges (viewBox 1600×1000), tuned so phones still see a few. */
export const ARAUCARIAS = {
    far: [
        { x: 300, y: 724, h: 74, seed: 3 },
        { x: 470, y: 712, h: 92, seed: 5 },
        { x: 700, y: 732, h: 64, seed: 7 },
        { x: 935, y: 720, h: 86, seed: 11 },
        { x: 1150, y: 682, h: 70, seed: 13 },
        { x: 1360, y: 700, h: 96, seed: 17 },
    ],
    near: [
        { x: 120, y: 812, h: 300, seed: 2 },
        { x: 290, y: 818, h: 200, seed: 4 },
        { x: 610, y: 864, h: 118, seed: 6 },
        { x: 1015, y: 858, h: 148, seed: 8 },
        { x: 1250, y: 828, h: 250, seed: 10 },
        { x: 1470, y: 806, h: 320, seed: 12 },
    ],
} as const;

export const SCENE_COLORS = C;

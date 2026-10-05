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
 * The yellow car: a 3-door Lada Niva, side view, facing right. Drawn on the grid of the reference
 * illustration the founders chose (520 × 400 px, car facing left, ground at y = 385) and mirrored by
 * the group transform, so the numbers can be checked against that picture: long hood falling to a
 * rounded nose, raked windshield, rounded rear roof corner, big round flared arches, side crease,
 * fuel door, C-pillar vents and steel wheels with a ring of holes.
 * Origin = ground, center. Width ≈ 140 after the transform.
 */
const NIVA = 'matrix(-0.28 0 0 0.28 78.7 -107.8)';
const NIVA_WHEELS = [110, 420];

export function YellowCarShape({ headlights = false }: { headlights?: boolean }) {
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
                    <path d="M66 -33 L124 -45 L124 -18 Z" fill={`url(#${beam})`} />
                </>
            )}
            <g transform={NIVA}>
                {/* underbody shadow and the dark wheel wells, behind the body */}
                <rect x={160} y={316} width={210} height={12} rx={6} fill={C.night} opacity={0.55} />
                {NIVA_WHEELS.map((cx) => (
                    <path key={cx} d={`M${cx + 60} 318 A62 62 0 0 0 ${cx - 60} 318 Z`} fill={C.night} />
                ))}
                {/* body: rounded nose, long hood, raked windshield, rounded rear roof, upright tailgate */}
                <path
                    d="M48 318 L48 262 Q48 244 64 240 L183 226 L238 162 Q242 156 252 156 L468 154 Q492 154 498 176
                       L514 290 L514 318 L480 318 A62 62 0 0 0 360 318 L170 318 A62 62 0 0 0 50 318 Z"
                    fill={C.car}
                />
                {/* flared arches */}
                {NIVA_WHEELS.map((cx) => (
                    <path
                        key={cx}
                        d={`M${cx + 64} 318 A66 66 0 0 0 ${cx - 64} 318`}
                        stroke={C.night}
                        strokeOpacity={0.28}
                        strokeWidth={5}
                        fill="none"
                    />
                ))}
                {/* side crease along the whole car, with its highlight */}
                <path d="M54 252 L512 250" stroke={C.night} strokeOpacity={0.18} strokeWidth={4} />
                <path d="M56 246 L510 244" stroke={C.moonlight} strokeOpacity={0.28} strokeWidth={2} />
                {/* roof gutter, roof and hood shine */}
                <path d="M258 163 L470 161" stroke={C.night} strokeOpacity={0.22} strokeWidth={2} />
                <path
                    d="M272 159 L452 157"
                    stroke={C.moonlight}
                    strokeOpacity={0.5}
                    strokeWidth={5}
                    strokeLinecap="round"
                />
                <path
                    d="M82 240 L170 230"
                    stroke={C.moonlight}
                    strokeOpacity={0.4}
                    strokeWidth={4}
                    strokeLinecap="round"
                />
                {/* windows: door window with the vent pane, B-pillar, big rear quarter window */}
                <path d="M200 226 L246 172 Q248 170 252 170 L328 170 L328 226 Z" fill={C.nightBlue} />
                <path d="M228 226 L240 176" stroke={C.car} strokeWidth={5} />
                <path d="M340 170 L446 170 Q462 170 466 186 L470 214 Q471 226 459 226 L340 226 Z" fill={C.nightBlue} />
                <path
                    d="M262 214 L288 180 M372 214 L398 180"
                    stroke={C.beamGlow}
                    strokeOpacity={0.35}
                    strokeWidth={6}
                    strokeLinecap="round"
                />
                {/* C-pillar vents */}
                <path
                    d="M478 182 L482 198 M486 182 L490 198 M494 184 L497 198"
                    stroke={C.night}
                    strokeOpacity={0.45}
                    strokeWidth={3}
                    strokeLinecap="round"
                />
                {/* door, handle, mirror and fuel door */}
                <path
                    d="M190 230 L190 302 Q190 316 206 316 L322 316 Q336 316 336 302 L336 230"
                    stroke={C.night}
                    strokeOpacity={0.3}
                    strokeWidth={3}
                    fill="none"
                />
                <rect x={304} y={236} width={22} height={6} rx={3} fill={C.night} opacity={0.55} />
                <rect x={190} y={208} width={15} height={13} rx={3} fill={C.night} opacity={0.8} />
                <rect
                    x={350}
                    y={262}
                    width={18}
                    height={16}
                    rx={3}
                    stroke={C.night}
                    strokeOpacity={0.35}
                    strokeWidth={2.5}
                    fill="none"
                />
                {/* headlight edge on the nose, tail light, black bumpers */}
                <rect x={46} y={258} width={6} height={16} rx={3} fill={C.moonlight} />
                <rect x={508} y={260} width={7} height={22} rx={2} fill={C.night} opacity={0.6} />
                <rect x={36} y={298} width={20} height={14} rx={4} fill={C.night} />
                <rect x={500} y={292} width={30} height={12} rx={4} fill={C.night} />
                {/* steel wheels: tyre, rim, ring of holes, hub */}
                {NIVA_WHEELS.map((cx) => (
                    <g key={cx}>
                        <circle cx={cx} cy={335} r={50} fill={C.night} />
                        <circle cx={cx} cy={335} r={31} fill={C.moonlight} opacity={0.85} />
                        <circle
                            cx={cx}
                            cy={335}
                            r={23}
                            fill="none"
                            stroke={C.night}
                            strokeOpacity={0.25}
                            strokeWidth={2}
                        />
                        {Array.from({ length: 8 }, (_, i) => (
                            <circle
                                key={i}
                                cx={cx + 16 * Math.cos((i * Math.PI) / 4)}
                                cy={335 + 16 * Math.sin((i * Math.PI) / 4)}
                                r={3.2}
                                fill={C.night}
                                opacity={0.55}
                            />
                        ))}
                        <circle cx={cx} cy={335} r={7} fill={C.night} opacity={0.7} />
                    </g>
                ))}
            </g>
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

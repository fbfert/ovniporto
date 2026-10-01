/**
 * Hand-drawn SVG primitives for the OVNIPORTO night scene. Every shape is drawn
 * around its own origin so it can be placed and animated inside any <svg>.
 * Colors are brand tokens only.
 */

const C = {
    moonlight: 'var(--color-moonlight)',
    night: 'var(--color-night)',
    nightBlue: 'var(--color-night-blue)',
    horizon: 'var(--color-horizon)',
    beam: 'var(--color-beam)',
    beamGlow: 'var(--color-beam-glow)',
    car: 'var(--color-car)',
} as const;

/** The yellow car of the legend: a round-backed '70s beetle, side view. Origin = ground, center. Width ≈ 140. */
export function YellowCarShape({ headlights = false }: { headlights?: boolean }) {
    return (
        <g>
            {headlights && (
                <path d="M66 -24 L150 -44 L150 -2 Z" fill={C.car} opacity={0.18} style={{ mixBlendMode: 'screen' }} />
            )}
            {/* body */}
            <path
                d="M-68 -12 C-70 -26 -60 -32 -46 -34 C-38 -54 -18 -64 4 -64 C28 -64 44 -54 52 -38 C62 -36 70 -30 70 -18 L70 -12 Z"
                fill={C.car}
            />
            {/* roof shine */}
            <path
                d="M-20 -58 C-8 -62 10 -62 22 -58"
                stroke={C.moonlight}
                strokeOpacity={0.55}
                strokeWidth={2.5}
                fill="none"
                strokeLinecap="round"
            />
            {/* windows */}
            <path d="M-34 -38 C-26 -52 -12 -57 2 -57 L2 -38 Z" fill={C.nightBlue} />
            <path d="M8 -57 C24 -56 36 -50 43 -38 L8 -38 Z" fill={C.nightBlue} />
            <path
                d="M-30 -42 L-22 -52"
                stroke={C.beamGlow}
                strokeOpacity={0.35}
                strokeWidth={2}
                strokeLinecap="round"
            />
            {/* door line, handle, running board */}
            <path d="M5 -38 L5 -16" stroke={C.night} strokeOpacity={0.35} strokeWidth={1.5} />
            <rect x={14} y={-33} width={8} height={2.4} rx={1.2} fill={C.night} opacity={0.5} />
            <rect x={-50} y={-14} width={102} height={4} rx={2} fill={C.night} opacity={0.35} />
            {/* fenders */}
            <path d="M-60 -12 C-60 -30 -24 -30 -24 -12 Z" fill={C.car} />
            <path d="M24 -12 C24 -30 62 -30 62 -12 Z" fill={C.car} />
            {/* wheels */}
            <circle cx={-42} cy={-9} r={12} fill={C.night} />
            <circle cx={43} cy={-9} r={12} fill={C.night} />
            <circle cx={-42} cy={-9} r={4.5} fill={C.moonlight} opacity={0.75} />
            <circle cx={43} cy={-9} r={4.5} fill={C.moonlight} opacity={0.75} />
            {/* headlight & tail light */}
            <circle cx={64} cy={-25} r={3.6} fill={C.moonlight} />
            <rect x={-70} y={-26} width={4} height={6} rx={1.5} fill={C.night} opacity={0.6} />
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

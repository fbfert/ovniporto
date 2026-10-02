import { motion, useMotionValue, useScroll, useTransform, type MotionValue } from 'motion/react';
import { useId, useRef } from 'react';
import { Seal } from '@/Components/Brand/Seal';
import { Picture } from '@/Components/Ui/Picture';
import { Badge } from '@/Components/Ui/Typography';
import { ARAUCARIAS, AraucariaShape, SaucerShape, SERRA, YellowCarShape } from '@/Components/Scene/Art';
import { Starfield } from '@/Components/Scene/Starfield';
import { usePrefersReducedMotion } from '@/hooks/usePrefersReducedMotion';
import { t } from '@/i18n/pt-BR';
import { easeFn } from '@/lib/motion';

const CAR = { x: 800, y: 873 };
const UFO_HOVER_Y = 430;
const CAR_LIFT = -(CAR.y - UFO_HOVER_Y - 30);
const PARTICLES = [-70, -42, -18, 6, 30, 52, 76, -56, 18, 64];
/** Scroll range where the illustrated cover hands over to the animated scene, before the saucer descends. */
const COVER_FADE = [0.06, 0.26];

/**
 * Section 01. One orchestrated moment, driven by scroll rather than time:
 * the seal lifts away, the saucer comes down the faint beam, the green light
 * opens and the yellow car of the legend goes up. Scrolling back reverses it.
 * The first frame is the concept illustration of the runway; it fades out as
 * the story starts so the drawn saucer never shares the sky with the painted one.
 * Reduced motion: the illustration alone, still, no pinning.
 */
export function AbductionHero() {
    const ref = useRef<HTMLElement>(null);
    const reduced = usePrefersReducedMotion();
    const { scrollYProgress } = useScroll({ target: ref, offset: ['start start', 'end end'] });
    const still = useMotionValue(0);
    const p = reduced ? still : scrollYProgress;

    return (
        <section
            ref={ref}
            data-tone="dark"
            aria-label={t.hero.sceneLabel}
            className={`relative bg-night text-moonlight ${reduced ? 'h-svh min-h-[640px]' : 'h-[210svh] min-h-[1300px]'}`}
        >
            <div className="sticky top-0 h-svh min-h-[640px] overflow-hidden">
                <Sky p={p} />
                <CoverArt p={p} />
                {!reduced && <Scene p={p} />}
                <Overlay p={p} />
            </div>
        </section>
    );
}

function Sky({ p }: { p: MotionValue<number> }) {
    const starsY = useTransform(p, [0, 1], ['translateY(0px)', 'translateY(-36px)']);
    return (
        <>
            <div
                aria-hidden
                className="absolute inset-0 bg-[radial-gradient(120%_80%_at_50%_0%,var(--color-night-blue)_0%,var(--color-night)_62%)]"
            />
            <motion.div aria-hidden className="absolute inset-0" style={{ transform: starsY }}>
                <Starfield density="medium" />
            </motion.div>
            {/* lilac haze along the horizon: the brand's horizon color at dusk */}
            <div
                aria-hidden
                className="absolute inset-x-0 bottom-0 h-[55%] bg-[linear-gradient(to_bottom,transparent,rgb(73_67_131/0.42)_70%,rgb(73_67_131/0.2))]"
            />
        </>
    );
}

function CoverArt({ p }: { p: MotionValue<number> }) {
    const opacity = useTransform(p, COVER_FADE, [1, 0]);
    const scale = useTransform(p, COVER_FADE, [1, 1.06]);
    return (
        <motion.div aria-hidden style={{ opacity }} className="absolute inset-0">
            <motion.div style={{ scale }} className="absolute inset-0">
                <Picture
                    slug="cover"
                    alt=""
                    priority
                    className="h-full w-full"
                    imgClassName="h-full w-full object-cover object-[50%_70%]"
                />
            </motion.div>
            {/* 30% night veil keeps the seal readable over the painting */}
            <div className="absolute inset-0 bg-night/30" />
            <div className="absolute inset-x-0 top-0 h-1/3 bg-[linear-gradient(to_bottom,rgb(6_17_33/0.55),transparent)]" />
            <Badge tone="horizon" className="absolute top-[5.5rem] right-4 sm:right-6">
                {t.concept.badge}
            </Badge>
        </motion.div>
    );
}

function Scene({ p }: { p: MotionValue<number> }) {
    const uid = useId().replace(/:/g, '');
    const beamGradient = `beam-${uid}`;
    const coreGradient = `beam-core-${uid}`;
    const halo = `halo-${uid}`;

    const farOpacity = useTransform(p, COVER_FADE, [0, 1]);
    const ufoY = useTransform(p, [0.1, 0.36, 0.86, 1], [-220, UFO_HOVER_Y, UFO_HOVER_Y, -260], {
        ease: [easeFn.snap, easeFn.linear, easeFn.glide],
    });
    const ufoX = useTransform(p, [0.86, 1], [0, 880], { ease: easeFn.glide });
    const ufoScale = useTransform(p, [0.86, 1], [1, 0.42]);

    const beamScale = useTransform(p, [0.34, 0.44, 0.8, 0.88], [0.04, 1, 1, 0]);
    const beamOpacity = useTransform(p, [0.32, 0.4, 0.82, 0.88], [0, 1, 1, 0]);

    const carY = useTransform(p, [0.46, 0.8], [0, CAR_LIFT], { ease: easeFn.glide });
    const carScale = useTransform(p, [0.46, 0.8], [1, 0.3]);
    const carRotate = useTransform(p, [0.38, 0.41, 0.44, 0.47, 0.52, 0.8], [0, -4, 3, -2, 0, -16]);
    const carOpacity = useTransform(p, [0.77, 0.83], [1, 0]);

    const shadowScale = useTransform(p, [0.44, 0.72], [1, 0.15]);
    const shadowOpacity = useTransform(p, [0.44, 0.72], [0.45, 0]);
    const groundGlow = useTransform(p, [0.36, 0.44, 0.8, 0.88], [0, 0.22, 0.22, 0]);

    const flashOpacity = useTransform(p, [0.79, 0.83, 0.9], [0, 1, 0]);
    const flashScale = useTransform(p, [0.79, 0.9], [0.6, 1.6]);

    return (
        <svg
            aria-hidden
            viewBox="0 0 1600 1000"
            preserveAspectRatio="xMidYMax slice"
            className="absolute inset-0 h-full w-full"
        >
            <defs>
                <linearGradient id={beamGradient} x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" stopColor="var(--color-beam)" stopOpacity="0.75" />
                    <stop offset="70%" stopColor="var(--color-beam)" stopOpacity="0.28" />
                    <stop offset="100%" stopColor="var(--color-beam-glow)" stopOpacity="0.12" />
                </linearGradient>
                <linearGradient id={coreGradient} x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" stopColor="var(--color-beam-glow)" stopOpacity="0.9" />
                    <stop offset="100%" stopColor="var(--color-beam-glow)" stopOpacity="0" />
                </linearGradient>
                <radialGradient id={halo}>
                    <stop offset="0%" stopColor="var(--color-beam-glow)" stopOpacity="0.9" />
                    <stop offset="100%" stopColor="var(--color-beam)" stopOpacity="0" />
                </radialGradient>
            </defs>

            {/* far ridge and trees: hidden while the illustration is on screen */}
            <motion.g style={{ opacity: farOpacity }}>
                <path d={SERRA.far} fill="var(--color-night-blue)" />
                {ARAUCARIAS.far.map((tree) => (
                    <g key={tree.x} transform={`translate(${tree.x} ${tree.y})`} opacity={0.9}>
                        <AraucariaShape height={tree.h} seed={tree.seed} fill="rgb(6 17 33 / 0.55)" />
                    </g>
                ))}
            </motion.g>

            {/* the beam (drawn behind the near ridge so the hill swallows its foot) */}
            <g transform={`translate(${CAR.x} ${UFO_HOVER_Y + 14})`}>
                <motion.g style={{ scaleX: beamScale, opacity: beamOpacity, originY: 0 }}>
                    <path d="M-44 0 L44 0 L170 470 L-170 470 Z" fill={`url(#${beamGradient})`} />
                    <path d="M-16 0 L16 0 L60 470 L-60 470 Z" fill={`url(#${coreGradient})`} />
                    <g transform="translate(0 440)">
                        {PARTICLES.map((x, i) => (
                            <circle
                                key={i}
                                cx={x}
                                cy={0}
                                r={i % 3 === 0 ? 2.6 : 1.6}
                                fill="var(--color-beam-glow)"
                                className="animate-rise"
                                style={{ animationDelay: `${i * 280}ms` }}
                            />
                        ))}
                    </g>
                </motion.g>
            </g>

            {/* near ridge */}
            <path d={SERRA.near} fill="var(--color-night)" />
            <motion.g style={{ opacity: farOpacity }}>
                {ARAUCARIAS.near.map((tree) => (
                    <g key={tree.x} transform={`translate(${tree.x} ${tree.y})`}>
                        <AraucariaShape height={tree.h} seed={tree.seed} />
                    </g>
                ))}
            </motion.g>

            {/* the car and its shadow */}
            <g transform={`translate(${CAR.x} ${CAR.y})`}>
                <motion.ellipse
                    cx={0}
                    cy={2}
                    rx={150}
                    ry={14}
                    fill="var(--color-beam)"
                    style={{ opacity: groundGlow }}
                />
                <motion.ellipse
                    cx={0}
                    cy={1}
                    rx={66}
                    ry={6}
                    fill="#000"
                    style={{ scaleX: shadowScale, opacity: shadowOpacity }}
                />
                <motion.g style={{ y: carY, scale: carScale, rotate: carRotate, opacity: carOpacity }}>
                    <YellowCarShape />
                </motion.g>
            </g>

            {/* the saucer */}
            <g transform={`translate(${CAR.x} 0)`}>
                <motion.g style={{ x: ufoX, y: ufoY, scale: ufoScale }}>
                    <motion.circle
                        r={120}
                        fill={`url(#${halo})`}
                        style={{ opacity: flashOpacity, scale: flashScale }}
                    />
                    <g className="animate-hover-bob">
                        <SaucerShape lightsClassName="animate-blink" />
                    </g>
                </motion.g>
            </g>
        </svg>
    );
}

function Overlay({ p }: { p: MotionValue<number> }) {
    const sealTransform = useTransform(p, [0, 0.3], ['translateY(0px) scale(1)', 'translateY(-140px) scale(0.76)']);
    const sealOpacity = useTransform(p, [0.08, 0.28], [1, 0]);
    const cueOpacity = useTransform(p, [0, 0.05], [1, 0]);
    const introBeamOpacity = useTransform(p, [0, 0.16], [1, 0]);
    const punchOpacity = useTransform(p, [0.9, 0.97], [0, 1]);
    const punchTransform = useTransform(p, [0.9, 0.97], ['translateY(14px)', 'translateY(0px)']);

    return (
        <>
            {/* faint beam waiting above the frame: the saucer is already up there */}
            <motion.div
                aria-hidden
                style={{ opacity: introBeamOpacity }}
                className="absolute top-0 left-1/2 h-[46%] w-[clamp(5rem,12vw,10rem)] -translate-x-1/2 animate-beam-pulse bg-[linear-gradient(to_bottom,rgb(84_201_51/0),rgb(84_201_51/0.12)_55%,rgb(84_201_51/0))] blur-md motion-reduce:hidden"
            />

            <motion.div
                style={{ transform: sealTransform, opacity: sealOpacity }}
                className="absolute inset-x-0 top-[clamp(5.5rem,14svh,9rem)] flex flex-col items-center gap-6"
            >
                <div className="animate-seal-in">
                    <Seal size="lg" glow />
                </div>
                <p className="animate-fade-up rounded-full border border-moonlight/30 bg-moonlight/15 px-5 py-1.5 font-script text-2xl leading-none text-moonlight [animation-delay:450ms]">
                    {t.hero.freeVigil}
                </p>
            </motion.div>

            <motion.p
                style={{ opacity: punchOpacity, transform: punchTransform }}
                className="absolute inset-x-0 bottom-[30%] px-6 text-center font-script text-[clamp(1.8rem,1.2rem+2.6vw,3.2rem)] leading-tight text-beam-glow"
            >
                {t.hero.punchline}
            </motion.p>

            <motion.div
                style={{ opacity: cueOpacity }}
                className="absolute inset-x-0 bottom-[max(1.25rem,env(safe-area-inset-bottom))] flex animate-fade-up flex-col items-center gap-1 text-moonlight/85 [animation-delay:800ms]"
            >
                <span className="font-script text-xl">{t.hero.scroll}</span>
                <svg
                    aria-hidden
                    viewBox="0 0 24 24"
                    className="size-5 animate-nudge motion-reduce:animate-none"
                    fill="none"
                >
                    <path
                        d="M6 9l6 6 6-6"
                        stroke="currentColor"
                        strokeWidth="2"
                        strokeLinecap="round"
                        strokeLinejoin="round"
                    />
                </svg>
            </motion.div>
        </>
    );
}

import { useEffect, useRef } from 'react';

type Density = 'low' | 'medium';

interface Star {
    x: number;
    y: number;
    layer: 0 | 1 | 2;
    r: number;
    alpha: number;
    speed: number;
    phase: number;
    tint: string;
}

interface Meteor {
    x: number;
    y: number;
    born: number;
    angle: number;
    length: number;
}

const BASE_COUNT: Record<Density, number> = { low: 90, medium: 160 };
const FRAME_MS = 1000 / 30;
const MAX_PARALLAX = 12;
const METEOR_MS = 750;
const MOONLIGHT = '244,245,232';
const BEAM_GLOW = '173,219,161';

function createStars(count: number): Star[] {
    return Array.from({ length: count }, () => {
        const layer = (Math.random() < 0.6 ? 0 : Math.random() < 0.75 ? 1 : 2) as Star['layer'];
        return {
            x: Math.random(),
            y: Math.random(),
            layer,
            r: [0.55, 0.9, 1.35][layer]! * (0.75 + Math.random() * 0.5),
            alpha: [0.35, 0.6, 0.9][layer]! * (0.7 + Math.random() * 0.3),
            speed: 0.4 + Math.random() * 1.4,
            phase: Math.random() * Math.PI * 2,
            tint: Math.random() < 0.1 ? BEAM_GLOW : MOONLIGHT,
        };
    });
}

/**
 * Night sky on a canvas: three depth layers, slow twinkle, a little scroll
 * parallax and a shooting star every 8–15s. Draws at ~30fps only while visible
 * and while the tab is in front; reduced motion gets a single still frame.
 */
export function Starfield({
    density = 'medium',
    parallax = false,
    className = '',
}: {
    density?: Density;
    parallax?: boolean;
    className?: string;
}) {
    const canvasRef = useRef<HTMLCanvasElement>(null);

    useEffect(() => {
        const canvas = canvasRef.current;
        const ctx = canvas?.getContext('2d');
        if (!canvas || !ctx) return;

        const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        let width = 0;
        let height = 0;
        let stars: Star[] = [];
        let meteor: Meteor | null = null;
        let nextMeteorAt = performance.now() + 4000 + Math.random() * 6000;
        let raf = 0;
        let last = 0;
        let visible = false;

        const resize = () => {
            const rect = canvas.getBoundingClientRect();
            const dpr = Math.min(window.devicePixelRatio || 1, 2);
            width = rect.width;
            height = rect.height;
            canvas.width = Math.round(width * dpr);
            canvas.height = Math.round(height * dpr);
            ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
            const areaFactor = Math.min(1.15, Math.max(0.45, (width * height) / (1440 * 900)));
            stars = createStars(Math.round(BASE_COUNT[density] * areaFactor));
            draw(performance.now());
        };

        const parallaxOffset = (): number => {
            if (!parallax || reduced) return 0;
            const rect = canvas.getBoundingClientRect();
            const progress = Math.max(-1, Math.min(1, rect.top / window.innerHeight));
            return progress * MAX_PARALLAX;
        };

        const drawMeteor = (now: number) => {
            if (!meteor) return;
            const t = (now - meteor.born) / METEOR_MS;
            if (t >= 1) {
                meteor = null;
                return;
            }
            const travel = meteor.length * 2.2 * t;
            const headX = meteor.x + Math.cos(meteor.angle) * travel;
            const headY = meteor.y + Math.sin(meteor.angle) * travel;
            const tailX = headX - Math.cos(meteor.angle) * meteor.length;
            const tailY = headY - Math.sin(meteor.angle) * meteor.length;
            const fade = Math.sin(Math.PI * t);
            const gradient = ctx.createLinearGradient(tailX, tailY, headX, headY);
            gradient.addColorStop(0, `rgba(${MOONLIGHT},0)`);
            gradient.addColorStop(1, `rgba(${MOONLIGHT},${0.85 * fade})`);
            ctx.strokeStyle = gradient;
            ctx.lineWidth = 1.4;
            ctx.lineCap = 'round';
            ctx.beginPath();
            ctx.moveTo(tailX, tailY);
            ctx.lineTo(headX, headY);
            ctx.stroke();
        };

        const draw = (now: number) => {
            ctx.clearRect(0, 0, width, height);
            const offset = parallaxOffset();
            const time = now / 1000;
            for (const star of stars) {
                const twinkle = reduced ? 1 : 0.62 + 0.38 * Math.sin(time * star.speed + star.phase);
                const y = star.y * height + offset * (star.layer + 1) * 0.5;
                ctx.fillStyle = `rgba(${star.tint},${(star.alpha * twinkle).toFixed(3)})`;
                ctx.beginPath();
                ctx.arc(star.x * width, y, star.r, 0, Math.PI * 2);
                ctx.fill();
            }
            if (!reduced) drawMeteor(now);
        };

        const tick = (now: number) => {
            raf = requestAnimationFrame(tick);
            if (now - last < FRAME_MS) return;
            last = now;
            if (!meteor && now >= nextMeteorAt) {
                meteor = {
                    x: width * (0.15 + Math.random() * 0.6),
                    y: height * (0.05 + Math.random() * 0.35),
                    born: now,
                    angle: Math.PI * (0.12 + Math.random() * 0.14),
                    length: 70 + Math.random() * 90,
                };
                nextMeteorAt = now + 8000 + Math.random() * 7000;
            }
            draw(now);
        };

        const start = () => {
            if (reduced || raf || !visible || document.hidden) return;
            raf = requestAnimationFrame(tick);
        };
        const stop = () => {
            cancelAnimationFrame(raf);
            raf = 0;
        };

        const observer = new IntersectionObserver(([entry]) => {
            visible = Boolean(entry?.isIntersecting);
            if (visible) start();
            else stop();
        });
        const onVisibility = () => (document.hidden ? stop() : start());
        const resizeObserver = new ResizeObserver(resize);

        // Decoration: the stars wait until the browser is idle, off the path of the first paint.
        const begin = () => {
            observer.observe(canvas);
            resizeObserver.observe(canvas);
            document.addEventListener('visibilitychange', onVisibility);
        };
        const hasIdle = typeof window.requestIdleCallback === 'function';
        const idle = hasIdle ? window.requestIdleCallback(begin, { timeout: 1500 }) : window.setTimeout(begin, 200);

        return () => {
            if (hasIdle) window.cancelIdleCallback(idle);
            else window.clearTimeout(idle);
            stop();
            observer.disconnect();
            resizeObserver.disconnect();
            document.removeEventListener('visibilitychange', onVisibility);
        };
    }, [density, parallax]);

    return <canvas ref={canvasRef} aria-hidden className={`pointer-events-none h-full w-full ${className}`} />;
}

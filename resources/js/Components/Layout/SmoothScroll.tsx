import Lenis from 'lenis';
import { router } from '@inertiajs/react';
import { useEffect } from 'react';

/**
 * Inertial scrolling on mouse/trackpad only. Touch keeps native scrolling and
 * reduced motion keeps the browser default. Resets to the top on page visits.
 */
export function SmoothScroll() {
    useEffect(() => {
        const fine = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
        const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (!fine || reduced) return;

        const lenis = new Lenis({ lerp: 0.11, wheelMultiplier: 0.95, anchors: true });
        let raf = requestAnimationFrame(function frame(time) {
            lenis.raf(time);
            raf = requestAnimationFrame(frame);
        });
        const off = router.on('navigate', () => lenis.scrollTo(0, { immediate: true }));

        return () => {
            off();
            cancelAnimationFrame(raf);
            lenis.destroy();
        };
    }, []);

    return null;
}

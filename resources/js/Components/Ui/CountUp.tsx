import { animate, useInView } from 'motion/react';
import { useEffect, useRef } from 'react';
import { ease } from '@/lib/motion';

/**
 * Counts from 0 to `value` the first time it scrolls into view. The server
 * renders the final number (crawlers and no-JS readers get the real count);
 * the client only rewinds to 0 when the number starts below the fold.
 */
export function CountUp({ value, className = '' }: { value: number; className?: string }) {
    const ref = useRef<HTMLSpanElement>(null);
    const inView = useInView(ref, { once: true, margin: '0px 0px -10% 0px' });
    const armed = useRef(false);

    useEffect(() => {
        const el = ref.current;
        if (!el || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
        const rect = el.getBoundingClientRect();
        if (rect.top > window.innerHeight) {
            el.textContent = '0';
            armed.current = true;
        }
    }, []);

    useEffect(() => {
        const el = ref.current;
        if (!inView || !armed.current || !el) return;
        const controls = animate(0, value, {
            duration: Math.min(1.6, 0.6 + value / 60),
            ease: ease.snap,
            onUpdate: (v) => {
                el.textContent = Math.round(v).toLocaleString('pt-BR');
            },
        });
        return () => controls.stop();
    }, [inView, value]);

    return (
        <span ref={ref} className={`tabular-nums ${className}`}>
            {value.toLocaleString('pt-BR')}
        </span>
    );
}

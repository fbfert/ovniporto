import { motion, type Variants } from 'motion/react';
import type { ReactNode } from 'react';
import { usePrefersReducedMotion } from '@/hooks/usePrefersReducedMotion';
import { duration, ease, STAGGER } from '@/lib/motion';

const container: Variants = {
    hidden: {},
    shown: { transition: { staggerChildren: STAGGER } },
};

export const revealItem: Variants = {
    hidden: { opacity: 0, transform: 'translateY(16px)' },
    shown: {
        opacity: 1,
        transform: 'translateY(0px)',
        transition: { duration: duration.reveal, ease: ease.snap },
    },
};

/**
 * Fades a block in once, when it enters the viewport. With `stagger`, direct
 * <RevealItem> children enter 80ms apart. Reduced motion renders content as-is.
 */
export function Reveal({
    children,
    stagger = false,
    className = '',
    as = 'div',
}: {
    children: ReactNode;
    stagger?: boolean;
    className?: string;
    as?: 'div' | 'ul' | 'ol';
}) {
    const reduced = usePrefersReducedMotion();
    const Tag = as === 'ul' ? motion.ul : as === 'ol' ? motion.ol : motion.div;

    if (reduced) {
        const Plain = as;
        return <Plain className={className}>{children}</Plain>;
    }

    return (
        <Tag
            className={className}
            initial="hidden"
            whileInView="shown"
            viewport={{ once: true, margin: '0px 0px -12% 0px' }}
            variants={stagger ? container : revealItem}
        >
            {children}
        </Tag>
    );
}

export function RevealItem({
    children,
    className = '',
    as = 'div',
}: {
    children: ReactNode;
    className?: string;
    as?: 'div' | 'li';
}) {
    const reduced = usePrefersReducedMotion();
    if (reduced) {
        const Plain = as;
        return <Plain className={className}>{children}</Plain>;
    }
    const Tag = as === 'li' ? motion.li : motion.div;
    return (
        <Tag className={className} variants={revealItem}>
            {children}
        </Tag>
    );
}

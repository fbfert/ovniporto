import { Link } from '@inertiajs/react';
import { AnimatePresence, motion } from 'motion/react';
import { useEffect, useRef } from 'react';
import { Starfield } from '@/Components/Scene/Starfield';
import { Button } from '@/Components/Ui/Button';
import { t } from '@/i18n/pt-BR';
import { ease } from '@/lib/motion';
import { JOIN_HREF, allLinks } from './nav';

/** Origin of the circular reveal: the menu button (top-right of the pill). */
const ORIGIN = 'calc(100% - 2.6rem) 2.6rem';

/**
 * Full-screen night panel that opens as a circle growing out of the menu button.
 * Focus is trapped inside; Esc and the close button return focus to the trigger.
 */
export function MobileMenu({ open, onClose }: { open: boolean; onClose: () => void }) {
    const panelRef = useRef<HTMLDivElement>(null);

    useEffect(() => {
        if (!open) return;
        const panel = panelRef.current;
        const previous = document.activeElement as HTMLElement | null;
        document.documentElement.style.overflow = 'hidden';
        const focusables = () =>
            Array.from(panel?.querySelectorAll<HTMLElement>('a[href], button:not([disabled])') ?? []);
        requestAnimationFrame(() => focusables()[0]?.focus());

        const onKey = (event: KeyboardEvent) => {
            if (event.key === 'Escape') {
                onClose();
                return;
            }
            if (event.key !== 'Tab') return;
            const items = focusables();
            const first = items[0];
            const last = items[items.length - 1];
            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last?.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first?.focus();
            }
        };
        document.addEventListener('keydown', onKey);
        return () => {
            document.removeEventListener('keydown', onKey);
            document.documentElement.style.overflow = '';
            (previous ?? document.getElementById('menu-trigger'))?.focus();
        };
    }, [open, onClose]);

    return (
        <AnimatePresence>
            {open && (
                <motion.div
                    id="mobile-menu"
                    ref={panelRef}
                    role="dialog"
                    aria-modal="true"
                    aria-label={t.nav.primary}
                    data-tone="dark"
                    initial={{ clipPath: `circle(0% at ${ORIGIN})` }}
                    animate={{ clipPath: `circle(150% at ${ORIGIN})` }}
                    exit={{ clipPath: `circle(0% at ${ORIGIN})` }}
                    transition={{ duration: 0.5, ease: ease.drawer }}
                    className="fixed inset-0 z-[60] flex flex-col overflow-y-auto bg-night text-moonlight lg:hidden"
                >
                    <Starfield className="absolute inset-0" density="low" />
                    <div className="relative flex items-center justify-between px-6 pt-6">
                        <span className="font-script text-2xl text-beam-glow">{t.brand.tagline}</span>
                        <button
                            type="button"
                            onClick={onClose}
                            aria-label={t.nav.closeMenu}
                            className="press inline-flex size-11 items-center justify-center rounded-full border border-moonlight/30"
                        >
                            <svg viewBox="0 0 24 24" className="size-5" fill="none" aria-hidden>
                                <path d="M6 6l12 12M18 6L6 18" stroke="currentColor" strokeWidth="2" strokeLinecap="round" />
                            </svg>
                        </button>
                    </div>
                    <ul className="relative mt-8 flex flex-col gap-1 px-6">
                        {allLinks.map((link, i) => (
                            <motion.li
                                key={link.href}
                                initial={{ opacity: 0, transform: 'translateY(14px)' }}
                                animate={{ opacity: 1, transform: 'translateY(0px)' }}
                                transition={{ delay: 0.12 + i * 0.04, duration: 0.4, ease: ease.snap }}
                            >
                                <Link
                                    href={link.href}
                                    onClick={onClose}
                                    className="block py-2 font-display text-[clamp(1.35rem,6vw,2rem)] leading-tight font-bold tracking-[0.04em] uppercase active:text-beam"
                                >
                                    {link.label}
                                </Link>
                            </motion.li>
                        ))}
                    </ul>
                    <div className="relative mt-auto flex flex-col gap-3 px-6 pt-10 pb-[max(2rem,env(safe-area-inset-bottom))]">
                        <Button href={JOIN_HREF} size="lg" onClick={onClose}>
                            {t.nav.join}
                        </Button>
                        <p className="text-center font-script text-xl text-beam-glow">{t.brand.signoff}</p>
                    </div>
                </motion.div>
            )}
        </AnimatePresence>
    );
}

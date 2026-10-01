import { Link, usePage } from '@inertiajs/react';
import { motion, useMotionValueEvent, useScroll } from 'motion/react';
import { useEffect, useRef, useState } from 'react';
import { Seal } from '@/Components/Brand/Seal';
import { Button } from '@/Components/Ui/Button';
import { t } from '@/i18n/pt-BR';
import { duration, ease } from '@/lib/motion';
import { MobileMenu } from './MobileMenu';
import { JOIN_HREF, primaryLinks } from './nav';

/**
 * Floating pill. Inverts to night over dark bands (each band carries
 * data-tone="dark"), hides while reading downward and returns on the way up.
 */
export function Header() {
    const [onDark, setOnDark] = useState(true);
    const [hidden, setHidden] = useState(false);
    const [menuOpen, setMenuOpen] = useState(false);
    const { scrollY } = useScroll();
    const lastY = useRef(0);
    const { url } = usePage();

    useMotionValueEvent(scrollY, 'change', (y) => {
        const delta = y - lastY.current;
        if (Math.abs(delta) > 6) {
            setHidden(delta > 0 && y > 120);
            lastY.current = y;
        }
    });

    useEffect(() => {
        const dark = new Set<Element>();
        const observer = new IntersectionObserver(
            (entries) => {
                for (const entry of entries) {
                    if (entry.isIntersecting) dark.add(entry.target);
                    else dark.delete(entry.target);
                }
                setOnDark(dark.size > 0);
            },
            // a thin band where the pill floats (top 32px → 6% of the viewport)
            { rootMargin: '-32px 0px -94% 0px' },
        );
        document.querySelectorAll('[data-tone="dark"]').forEach((el) => observer.observe(el));
        return () => observer.disconnect();
    }, [url]);

    const toneClasses = onDark
        ? 'bg-night/85 text-moonlight border-moonlight/12'
        : 'bg-moonlight/85 text-night border-night/10';

    return (
        <>
            <motion.header
                initial={false}
                animate={{ transform: hidden && !menuOpen ? 'translateY(-140%)' : 'translateY(0%)' }}
                transition={{ duration: duration.panel, ease: ease.snap }}
                className="fixed inset-x-0 top-0 z-50 px-3 pt-3 sm:px-4 sm:pt-4"
            >
                <nav
                    aria-label={t.nav.primary}
                    className={`mx-auto grid h-16 max-w-6xl grid-cols-[1fr_auto_1fr] items-center rounded-full border px-2 backdrop-blur-[12px] transition-colors duration-300 ease-snap lg:px-3 ${toneClasses}`}
                >
                    <ul className="hidden items-center gap-1 lg:flex">
                        {primaryLinks.map((link) => (
                            <li key={link.href}>
                                <Link
                                    href={link.href}
                                    prefetch
                                    className="group relative inline-flex h-11 items-center rounded-full px-3 text-[0.92rem] font-medium"
                                >
                                    {link.label}
                                    <span
                                        aria-hidden
                                        className="absolute inset-x-3 bottom-2.5 h-px origin-left scale-x-0 bg-current transition-transform duration-300 ease-snap group-hover:scale-x-100"
                                    />
                                </Link>
                            </li>
                        ))}
                    </ul>
                    <span className="lg:hidden" />

                    <Link
                        href="/"
                        aria-label={t.nav.home}
                        className="press rounded-full transition-transform duration-500 ease-snap [@media(hover:hover)]:hover:rotate-[-12deg]"
                    >
                        <Seal size="sm" />
                    </Link>

                    <div className="flex items-center justify-end gap-2">
                        <span className="hidden lg:block">
                            <Button href={JOIN_HREF} size="sm">
                                {t.nav.join}
                            </Button>
                        </span>
                        <button
                            type="button"
                            aria-expanded={menuOpen}
                            aria-controls="mobile-menu"
                            aria-label={menuOpen ? t.nav.closeMenu : t.nav.openMenu}
                            onClick={() => setMenuOpen(true)}
                            className="inline-flex size-11 press items-center justify-center rounded-full lg:hidden"
                            id="menu-trigger"
                        >
                            <svg viewBox="0 0 24 24" className="size-6" fill="none" aria-hidden>
                                <path d="M4 8h16M4 16h11" stroke="currentColor" strokeWidth="2" strokeLinecap="round" />
                            </svg>
                        </button>
                    </div>
                </nav>
            </motion.header>
            <MobileMenu open={menuOpen} onClose={() => setMenuOpen(false)} />
        </>
    );
}

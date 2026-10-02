import { AnimatePresence, motion } from 'motion/react';
import { useId, useRef, useSyncExternalStore, type ReactNode } from 'react';
import { createPortal } from 'react-dom';
import { CloseIcon } from '@/Components/Icons';
import { useFocusTrap } from '@/hooks/useFocusTrap';
import { usePrefersReducedMotion } from '@/hooks/usePrefersReducedMotion';
import { t } from '@/i18n/pt-BR';
import { ease } from '@/lib/motion';

const noopSubscribe = () => () => {};

/**
 * Accessible dialog: focus moves in and stays in, Esc / backdrop / close button
 * dismiss, focus returns to the trigger, the page behind doesn't scroll.
 * Enters in 220ms from scale(0.96) at the centre and leaves faster (160ms).
 */
export function Modal({
    open,
    onClose,
    title,
    tone = 'dark',
    children,
    className = '',
}: {
    open: boolean;
    onClose: () => void;
    title: ReactNode;
    tone?: 'light' | 'dark';
    children: ReactNode;
    className?: string;
}) {
    const panelRef = useRef<HTMLDivElement>(null);
    const titleId = useId();
    const reduced = usePrefersReducedMotion();
    // false during SSR and hydration, true afterwards: the portal needs document.body
    const mounted = useSyncExternalStore(
        noopSubscribe,
        () => true,
        () => false,
    );
    useFocusTrap(panelRef, open, onClose);

    if (!mounted) return null;

    const hidden = reduced ? { opacity: 0 } : { opacity: 0, transform: 'scale(0.96)' };
    const shown = reduced ? { opacity: 1 } : { opacity: 1, transform: 'scale(1)' };
    const dark = tone === 'dark';

    return createPortal(
        <AnimatePresence>
            {open && (
                <motion.div
                    key="modal"
                    initial={{ opacity: 1 }}
                    exit={{ opacity: 1, transition: { duration: 0.16 } }}
                    className="fixed inset-0 z-[70] flex items-end justify-center p-4 sm:items-center"
                >
                    <motion.div
                        aria-hidden
                        onClick={onClose}
                        initial={{ opacity: 0 }}
                        animate={{ opacity: 1, transition: { duration: 0.22, ease: ease.snap } }}
                        exit={{ opacity: 0, transition: { duration: 0.16, ease: ease.snap } }}
                        className="absolute inset-0 bg-night/70 backdrop-blur-[2px]"
                    />
                    <motion.div
                        ref={panelRef}
                        role="dialog"
                        aria-modal="true"
                        aria-labelledby={titleId}
                        tabIndex={-1}
                        data-tone={tone}
                        initial={hidden}
                        animate={{ ...shown, transition: { duration: 0.22, ease: ease.snap } }}
                        exit={{ ...hidden, transition: { duration: 0.16, ease: ease.snap } }}
                        className={`relative max-h-[calc(100svh-2rem)] w-full max-w-lg overflow-y-auto rounded-[28px] p-6 shadow-lift outline-none sm:p-8 ${
                            dark ? 'bg-night-blue text-moonlight ring-1 ring-moonlight/12' : 'bg-moonlight text-night'
                        } ${className}`}
                    >
                        <div className="mb-5 flex items-start justify-between gap-4">
                            <h2
                                id={titleId}
                                className="font-display text-xl leading-tight font-bold tracking-[0.03em] uppercase"
                            >
                                {title}
                            </h2>
                            <button
                                type="button"
                                onClick={onClose}
                                aria-label={t.form.close}
                                className={`-mt-1 -mr-2 inline-flex size-11 shrink-0 press items-center justify-center rounded-full ${
                                    dark ? 'text-moonlight/80 hover:bg-moonlight/10' : 'text-night/70 hover:bg-night/5'
                                }`}
                            >
                                <CloseIcon />
                            </button>
                        </div>
                        {children}
                    </motion.div>
                </motion.div>
            )}
        </AnimatePresence>,
        document.body,
    );
}

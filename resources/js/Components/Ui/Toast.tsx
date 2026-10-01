import { AnimatePresence, motion } from 'motion/react';
import { createContext, useCallback, useContext, useEffect, useRef, useState, type ReactNode } from 'react';
import { t } from '@/i18n/pt-BR';
import { duration, ease } from '@/lib/motion';

interface ToastState {
    id: number;
    message: string;
}

const ToastContext = createContext<(message: string) => void>(() => undefined);

export const useToast = () => useContext(ToastContext);

/** One toast at a time, bottom center, enters and leaves through the bottom edge. */
export function ToastProvider({ children }: { children: ReactNode }) {
    const [toast, setToast] = useState<ToastState | null>(null);
    const timer = useRef<number | undefined>(undefined);

    const show = useCallback((message: string) => {
        window.clearTimeout(timer.current);
        setToast({ id: Date.now(), message });
        timer.current = window.setTimeout(() => setToast(null), 4200);
    }, []);

    useEffect(() => () => window.clearTimeout(timer.current), []);

    return (
        <ToastContext.Provider value={show}>
            {children}
            <div
                role="status"
                aria-live="polite"
                className="pointer-events-none fixed inset-x-0 bottom-[max(1.25rem,env(safe-area-inset-bottom))] z-[70] flex justify-center px-4"
            >
                <AnimatePresence>
                    {toast && (
                        <motion.div
                            key={toast.id}
                            initial={{ opacity: 0, transform: 'translateY(120%)' }}
                            animate={{ opacity: 1, transform: 'translateY(0%)' }}
                            exit={{ opacity: 0, transform: 'translateY(120%)' }}
                            transition={{ duration: duration.ui + 0.08, ease: ease.snap }}
                            className="pointer-events-auto flex items-center gap-3 rounded-full bg-night-blue py-2 pr-2 pl-5 text-moonlight shadow-[0_18px_40px_-16px_rgb(6_17_33/0.8)] ring-1 ring-beam/40"
                        >
                            <span aria-hidden className="size-2 rounded-full bg-beam shadow-[0_0_10px_var(--color-beam)]" />
                            <span className="text-[0.95rem] font-medium">{toast.message}</span>
                            <button
                                type="button"
                                onClick={() => setToast(null)}
                                aria-label={t.toast.close}
                                className="press inline-flex size-9 items-center justify-center rounded-full hover:bg-moonlight/10"
                            >
                                <svg viewBox="0 0 24 24" className="size-4" fill="none" aria-hidden>
                                    <path d="M6 6l12 12M18 6L6 18" stroke="currentColor" strokeWidth="2" strokeLinecap="round" />
                                </svg>
                            </button>
                        </motion.div>
                    )}
                </AnimatePresence>
            </div>
        </ToastContext.Provider>
    );
}

import { useEffect, type RefObject } from 'react';

const FOCUSABLE =
    'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';

/**
 * While `active`: moves focus into the container, keeps Tab cycling inside it,
 * calls `onEscape` on Esc and locks page scroll. On release, focus returns to
 * whatever had it before (or to `fallbackId`).
 */
export function useFocusTrap(
    ref: RefObject<HTMLElement | null>,
    active: boolean,
    onEscape: () => void,
    fallbackId?: string,
) {
    useEffect(() => {
        if (!active) return;
        const container = ref.current;
        const previous = document.activeElement as HTMLElement | null;
        const focusables = () => Array.from(container?.querySelectorAll<HTMLElement>(FOCUSABLE) ?? []);
        document.documentElement.style.overflow = 'hidden';
        // the container is already committed when the effect runs: focus now, no frame wait
        (focusables()[0] ?? container)?.focus();

        const onKey = (event: KeyboardEvent) => {
            if (event.key === 'Escape') {
                onEscape();
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
            (previous ?? (fallbackId ? document.getElementById(fallbackId) : null))?.focus();
        };
    }, [ref, active, onEscape, fallbackId]);
}

import { useEffect, useRef } from 'react';

export type ShortcutMap = Partial<Record<string, () => void>>;

/** Typing in a field, or anything inside an open dialog, is never a shortcut. */
function isTyping(target: EventTarget | null): boolean {
    if (!(target instanceof HTMLElement)) return false;
    if (target.isContentEditable) return true;
    if (target.closest('input, textarea, select, [role="dialog"]')) return true;
    return false;
}

/**
 * Single-key shortcuts for operators (lowercase keys: "a", "j"…). No animation
 * follows them: they are pressed hundreds of times a day.
 */
export function useShortcuts(shortcuts: ShortcutMap, enabled = true) {
    const latest = useRef(shortcuts);
    useEffect(() => {
        latest.current = shortcuts;
    });

    useEffect(() => {
        if (!enabled) return;
        const onKeyDown = (event: KeyboardEvent) => {
            if (event.defaultPrevented || event.ctrlKey || event.metaKey || event.altKey) return;
            if (isTyping(event.target)) return;
            const run = latest.current[event.key.toLowerCase()];
            if (!run) return;
            event.preventDefault();
            run();
        };
        window.addEventListener('keydown', onKeyDown);
        return () => window.removeEventListener('keydown', onKeyDown);
    }, [enabled]);
}

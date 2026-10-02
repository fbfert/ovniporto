import { useEffect, useRef, useState } from 'react';

/**
 * State mirrored to localStorage on every change and restored on the next
 * visit, so a report survives a dropped signal or a closed tab. Storage that
 * throws (private mode, quota) just means no draft.
 */
export function useDraft<T extends object>(key: string, initial: T) {
    const [restored] = useState<T | null>(() => {
        try {
            const raw = typeof window === 'undefined' ? null : window.localStorage.getItem(key);
            return raw ? ({ ...initial, ...(JSON.parse(raw) as Partial<T>) } as T) : null;
        } catch {
            return null;
        }
    });
    const [state, setState] = useState<T>(restored ?? initial);
    const cleared = useRef(false);

    useEffect(() => {
        if (cleared.current) return;
        try {
            window.localStorage.setItem(key, JSON.stringify(state));
        } catch {
            // no storage: the report still works, it just won't be remembered
        }
    }, [key, state]);

    const clear = () => {
        cleared.current = true;
        try {
            window.localStorage.removeItem(key);
        } catch {
            // nothing to clear
        }
    };

    return { state, setState, wasRestored: restored !== null, clear };
}

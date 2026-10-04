import type { VisitOptions } from '@inertiajs/core';
import { useCallback, useRef, useState } from 'react';

export type ListRequestStatus = 'idle' | 'loading' | 'error';

type Callbacks = Pick<VisitOptions, 'onStart' | 'onSuccess' | 'onHttpException' | 'onNetworkError' | 'onFinish'>;

/**
 * Follows the Inertia request that refreshes a list (filters, "load more"): loading while
 * it runs, error when the server or the network fails (the list shows its error state
 * instead of Inertia's modal), and `retry` repeats the last request.
 */
export function useListRequest() {
    const [status, setStatus] = useState<ListRequestStatus>('idle');
    const last = useRef<((callbacks: Callbacks) => void) | null>(null);

    const run = useCallback((request: (callbacks: Callbacks) => void) => {
        last.current = request;
        request({
            onStart: () => setStatus('loading'),
            onSuccess: () => setStatus('idle'),
            onHttpException: () => {
                setStatus('error');
                return false;
            },
            onNetworkError: () => {
                setStatus('error');
                return false;
            },
        });
    }, []);

    const retry = useCallback(() => {
        if (last.current) run(last.current);
    }, [run]);

    return { status, run, retry };
}

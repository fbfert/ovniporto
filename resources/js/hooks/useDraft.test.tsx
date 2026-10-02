import { act, cleanup, renderHook } from '@testing-library/react';
import { afterEach, beforeEach, describe, expect, it } from 'vitest';
import { useDraft } from './useDraft';

beforeEach(() => window.localStorage.clear());
afterEach(cleanup);

describe('useDraft', () => {
    it('restores what was typed before the page was closed', () => {
        const first = renderHook(() => useDraft('relato', { step: 1, description: '' }));
        act(() => first.result.current.setState({ step: 3, description: 'Uma luz verde parada sobre a serra' }));
        first.unmount();

        const second = renderHook(() => useDraft('relato', { step: 1, description: '' }));

        expect(second.result.current.wasRestored).toBe(true);
        expect(second.result.current.state).toEqual({ step: 3, description: 'Uma luz verde parada sobre a serra' });
    });

    it('forgets the draft once it is cleared', () => {
        const first = renderHook(() => useDraft('relato', { step: 1 }));
        act(() => first.result.current.setState({ step: 4 }));
        act(() => first.result.current.clear());
        first.unmount();

        const second = renderHook(() => useDraft('relato', { step: 1 }));

        expect(second.result.current.wasRestored).toBe(false);
        expect(second.result.current.state).toEqual({ step: 1 });
    });
});

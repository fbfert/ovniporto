import { act, cleanup, render, renderHook, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { afterEach, describe, expect, it, vi } from 'vitest';
import { useListRequest } from '@/hooks/useListRequest';
import { ListState } from './ListState';

vi.mock('@inertiajs/react', () => ({
    Link: ({ href, children }: { href: string; children: React.ReactNode }) => <a href={href}>{children}</a>,
}));

afterEach(cleanup);

const items = <ul aria-label="Relatos">{<li>Luz sobre a serra</li>}</ul>;
const empty = <p>O Livro ainda está em branco.</p>;

describe('ListState', () => {
    it('shows placeholders in the section tone while loading, and tells screen readers', () => {
        const { container } = render(
            <ListState status="loading" tone="dark" isEmpty={false} empty={empty} onRetry={() => {}} placeholders={3}>
                {items}
            </ListState>,
        );

        expect(screen.getByRole('status').textContent).toBe('Carregando…');
        expect(container.querySelector('[aria-busy="true"]')).not.toBeNull();
        const placeholders = container.querySelectorAll('[aria-busy] > div');
        expect(placeholders).toHaveLength(3);
        expect(placeholders[0]?.className).toContain('bg-moonlight/[0.08]');
        expect(screen.queryByText('Luz sobre a serra')).toBeNull();
    });

    it('explains the failure in Portuguese and retries', async () => {
        const onRetry = vi.fn();
        render(
            <ListState status="error" isEmpty={false} empty={empty} onRetry={onRetry}>
                {items}
            </ListState>,
        );

        expect(screen.getByRole('alert').textContent).toContain('Sem sinal da torre agora');
        await userEvent.setup().click(screen.getByRole('button', { name: 'Tentar de novo' }));
        expect(onRetry).toHaveBeenCalledOnce();
    });

    it('shows the list invitation when there is nothing, and the items otherwise', () => {
        const { rerender } = render(
            <ListState status="idle" isEmpty empty={empty} onRetry={() => {}}>
                {items}
            </ListState>,
        );
        expect(screen.getByText('O Livro ainda está em branco.')).toBeTruthy();

        rerender(
            <ListState status="idle" isEmpty={false} empty={empty} onRetry={() => {}}>
                {items}
            </ListState>,
        );
        expect(screen.getByText('Luz sobre a serra')).toBeTruthy();
    });
});

describe('useListRequest', () => {
    it('goes loading, then error on a network failure without Inertia’s modal, and retries the same request', () => {
        const { result } = renderHook(() => useListRequest());
        const request = vi.fn();

        act(() => result.current.run(request));
        const callbacks = request.mock.lastCall?.[0];
        act(() => callbacks.onStart());
        expect(result.current.status).toBe('loading');

        let handled: boolean | void = undefined;
        act(() => {
            handled = callbacks.onNetworkError(new Error('offline'));
        });
        expect(handled).toBe(false);
        expect(result.current.status).toBe('error');

        act(() => result.current.retry());
        expect(request).toHaveBeenCalledTimes(2);
        act(() => request.mock.lastCall?.[0].onSuccess());
        expect(result.current.status).toBe('idle');
    });
});

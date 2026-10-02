import { cleanup, render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { forwardRef, type ReactNode } from 'react';
import { afterEach, describe, expect, it, vi } from 'vitest';
import { QueueList, type QueueItem } from './QueueList';

vi.mock('@inertiajs/react', () => ({
    Link: forwardRef<HTMLAnchorElement, { href: string; children: ReactNode; className?: string }>(
        ({ href, children, ...rest }, ref) => (
            <a href={href} ref={ref} {...rest}>
                {children}
            </a>
        ),
    ),
}));

afterEach(cleanup);

const item = (id: number, nickname: string): QueueItem => ({
    id,
    type: 'light',
    nickname,
    observedDate: '2026-09-30',
    city: 'Lages, SC',
    waitingHours: 3,
    thumb: null,
    photoCount: 0,
});

const ITEMS = [item(1, 'coruja'), item(2, 'farol'), item(3, 'vigia')];

describe('QueueList', () => {
    it('moves the focus to the next report with J and back with K', async () => {
        const user = userEvent.setup();
        render(<QueueList items={ITEMS} />);
        const links = screen.getAllByRole('link');

        await user.keyboard('j');
        expect(document.activeElement).toBe(links[0]);
        await user.keyboard('j');
        expect(document.activeElement).toBe(links[1]);
        await user.keyboard('k');
        expect(document.activeElement).toBe(links[0]);
    });

    it('stops at the ends of the queue', async () => {
        const user = userEvent.setup();
        render(<QueueList items={ITEMS} />);
        const links = screen.getAllByRole('link');

        await user.keyboard('k');
        expect(document.activeElement).toBe(links[2]);
        await user.keyboard('j');
        expect(document.activeElement).toBe(links[2]);
    });

    it('ignores the keys while typing in a field', async () => {
        const user = userEvent.setup();
        render(
            <>
                <input aria-label="busca" />
                <QueueList items={ITEMS} />
            </>,
        );

        await user.click(screen.getByLabelText('busca'));
        await user.keyboard('j');

        expect(document.activeElement).toBe(screen.getByLabelText('busca'));
    });

    it('links each row to its review screen', () => {
        render(<QueueList items={ITEMS} />);

        expect(screen.getAllByRole('link').map((link) => link.getAttribute('href'))).toEqual([
            '/painel/relatos/1',
            '/painel/relatos/2',
            '/painel/relatos/3',
        ]);
    });
});

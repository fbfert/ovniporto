import { cleanup, render, screen } from '@testing-library/react';
import type { ReactNode } from 'react';
import { afterEach, describe, expect, it, vi } from 'vitest';
import Campaign from './Campaign';

vi.mock('@inertiajs/react', () => ({
    Head: () => null,
    Link: ({ href, children }: { href: string; children: ReactNode }) => <a href={href}>{children}</a>,
    router: { delete: vi.fn(), post: vi.fn() },
    useForm: <T extends object>(initial: T) => ({
        data: initial,
        errors: {},
        processing: false,
        setData: vi.fn(),
        put: vi.fn(),
        post: vi.fn(),
        reset: vi.fn(),
    }),
}));

afterEach(cleanup);

const SETTINGS = {
    status: 'planning',
    goalCents: null,
    raisedCents: null,
    crowdfundingUrl: null,
    storeSharePercent: null,
};

describe('Campaign panel', () => {
    it('always shows the fixed warning, whatever the campaign state', () => {
        for (const status of ['planning', 'open', 'closed']) {
            render(<Campaign settings={{ ...SETTINGS, status }} supporters={[]} sponsors={[]} />);

            expect(screen.getByRole('note').textContent).toBe('Não abra a campanha sem orçamento aprovado.');
            cleanup();
        }
    });

    it('tells which supporters have a public name', () => {
        render(
            <Campaign
                settings={SETTINGS}
                supporters={[
                    { id: 1, name: 'Ana', amountCents: 5000, reward: null, publishName: true, supportedAt: null },
                    { id: 2, name: 'Bruno', amountCents: null, reward: null, publishName: false, supportedAt: null },
                ]}
                sponsors={[]}
            />,
        );

        expect(screen.getByText('nome público')).toBeTruthy();
        expect(screen.getByText('nome privado')).toBeTruthy();
    });
});

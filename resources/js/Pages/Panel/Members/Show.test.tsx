import { cleanup, render, screen } from '@testing-library/react';
import type { ReactNode } from 'react';
import { afterEach, describe, expect, it, vi } from 'vitest';
import Show from './Show';

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

const MEMBER = {
    id: 7,
    nickname: 'coruja',
    name: 'Ana Souza',
    email: 'ana@serra.test',
    avatarUrl: null,
    city: 'Lages',
    role: 'member',
    joinedAt: '2026-03-01',
    termsAcceptedAt: '2026-03-01T10:00:00-03:00',
    blockedAt: null,
    blockedReason: null,
    anonymizedAt: null,
    approved: 1,
    sightings: [],
};

describe('Member page in the panel', () => {
    it('shows an anonymized account without personal data or actions', () => {
        render(
            <Show
                member={{ ...MEMBER, name: null, email: null, city: null, anonymizedAt: '2031-03-01' }}
                history={[]}
                canManage
                isSelf={false}
            />,
        );

        expect(screen.getByRole('heading', { level: 1 }).textContent).toBe('@coruja');
        expect(screen.getAllByText('Conta anonimizada').length).toBeGreaterThan(0);
        expect(screen.queryByText(/ana@serra\.test/)).toBeNull();
        expect(screen.queryByRole('button', { name: 'Excluir conta' })).toBeNull();
        expect(screen.queryByRole('button', { name: 'Bloquear' })).toBeNull();
        expect(screen.queryByRole('button', { name: 'Salvar papel' })).toBeNull();
    });

    it('keeps the actions for an ordinary account', () => {
        render(<Show member={MEMBER} history={[]} canManage isSelf={false} />);

        expect(screen.getByText(/ana@serra\.test/)).toBeTruthy();
        expect(screen.getByRole('button', { name: 'Excluir conta' })).toBeTruthy();
        expect(screen.queryByText('Conta anonimizada')).toBeNull();
    });
});

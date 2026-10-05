import { render, screen } from '@testing-library/react';
import type { ReactNode } from 'react';
import { describe, expect, it, vi } from 'vitest';
import { t } from '@/i18n/pt-BR';
import { Footer } from './Footer';

vi.mock('@inertiajs/react', () => ({
    Link: ({ href, children, ...rest }: { href: string; children: ReactNode }) => (
        <a href={href} {...rest}>
            {children}
        </a>
    ),
    usePage: () => ({
        props: { community: { whatsapp: null, instagram: null, email: 'contato@ovniporto.tars.art.br' } },
    }),
}));

describe('Footer', () => {
    it('places the future runway in the Pedras Brancas locality', () => {
        render(<Footer />);

        expect(screen.getByText(/Localidade Pedras Brancas · Lages, SC/)).toBeTruthy();
        expect(document.body.textContent).not.toMatch(/Vila das Pedras/);
    });

    it('uses the same locality on the cover of /o-lugar', () => {
        expect(t.placePage.eyebrow).toBe('Na Localidade Pedras Brancas');
        expect(JSON.stringify(t)).not.toMatch(/Vila das Pedras/);
    });
});

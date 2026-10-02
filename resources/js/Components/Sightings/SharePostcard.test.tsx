import { cleanup, render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { afterEach, describe, expect, it, vi } from 'vitest';
import { SharePostcard, whatsappShareUrl } from './SharePostcard';

vi.mock('@inertiajs/react', () => ({
    Link: ({ href, children, ...rest }: { href: string; children: React.ReactNode }) => (
        <a href={href} {...rest}>
            {children}
        </a>
    ),
}));

afterEach(cleanup);

const URL = 'https://ovniporto.tars.art.br/relatos/42';

describe('SharePostcard', () => {
    it('opens WhatsApp with the ready text and the report link', () => {
        expect(whatsappShareUrl(URL)).toBe(
            `https://wa.me/?text=${encodeURIComponent(`Olha o que viram no céu de Lages: ${URL}`)}`,
        );

        render(<SharePostcard url={URL} />);
        const link = screen.getByRole('link', { name: /WhatsApp/ });
        const text = new globalThis.URL(link.getAttribute('href')!).searchParams.get('text');

        expect(text).toBe(`Olha o que viram no céu de Lages: ${URL}`);
    });

    it('copies the bare link', async () => {
        const user = userEvent.setup();
        const writeText = vi.fn().mockResolvedValue(undefined);
        Object.defineProperty(navigator, 'clipboard', { value: { writeText }, configurable: true });
        render(<SharePostcard url={URL} />);

        await user.click(screen.getByRole('button', { name: 'Copiar link' }));

        expect(writeText).toHaveBeenCalledWith(URL);
        expect(screen.getAllByText('Link copiado.').length).toBeGreaterThan(0);
    });
});

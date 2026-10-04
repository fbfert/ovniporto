import { cleanup, render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { afterEach, describe, expect, it, vi } from 'vitest';
import { PostcardShare, postcardWhatsappUrl } from './PostcardShare';

vi.mock('@inertiajs/react', () => ({
    Link: ({ href, children, ...rest }: { href: string; children: React.ReactNode }) => (
        <a href={href} {...rest}>
            {children}
        </a>
    ),
}));

const URL = 'https://ovniporto.tars.art.br/postal';
const IMAGE = '/brand/postal.jpg';

afterEach(() => {
    cleanup();
    Reflect.deleteProperty(navigator, 'share');
    Reflect.deleteProperty(navigator, 'canShare');
});

describe('PostcardShare', () => {
    it('offers WhatsApp and copy link when the browser has no native sharing', () => {
        render(<PostcardShare url={URL} imageUrl={IMAGE} />);

        const whatsapp = screen.getByRole('link', { name: /WhatsApp/ });
        expect(new globalThis.URL(whatsapp.getAttribute('href')!).searchParams.get('text')).toBe(
            `Guardei um lugar pra você no céu de Lages: ${URL}`,
        );
        expect(whatsapp.getAttribute('href')).toBe(postcardWhatsappUrl(URL));
        expect(screen.getByRole('button', { name: 'Copiar link' })).toBeTruthy();
        expect(screen.queryByRole('button', { name: 'Compartilhar postal' })).toBeNull();
    });

    it('copies the postcard link', async () => {
        const user = userEvent.setup();
        const writeText = vi.fn().mockResolvedValue(undefined);
        Object.defineProperty(navigator, 'clipboard', { value: { writeText }, configurable: true });
        render(<PostcardShare url={URL} imageUrl={IMAGE} />);

        await user.click(screen.getByRole('button', { name: 'Copiar link' }));

        expect(writeText).toHaveBeenCalledWith(URL);
        expect(screen.getAllByText('Link copiado.').length).toBeGreaterThan(0);
    });

    it('opens the native share sheet with the image when the device can share files', async () => {
        const user = userEvent.setup();
        const share = vi.fn().mockResolvedValue(undefined);
        Object.defineProperty(navigator, 'share', { value: share, configurable: true });
        Object.defineProperty(navigator, 'canShare', { value: () => true, configurable: true });
        vi.stubGlobal(
            'fetch',
            vi.fn().mockResolvedValue({ blob: async () => new Blob(['x'], { type: 'image/jpeg' }) }),
        );
        render(<PostcardShare url={URL} imageUrl={IMAGE} />);

        expect(screen.queryByRole('link', { name: /WhatsApp/ })).toBeNull();
        await user.click(screen.getByRole('button', { name: 'Compartilhar postal' }));

        const data = share.mock.lastCall?.[0] as ShareData;
        expect(data.files?.[0]?.name).toBe('postal-ovniporto.jpg');
        expect(data.text).toContain(URL);
        vi.unstubAllGlobals();
    });

    it('shares the link when the device cannot carry files', async () => {
        const user = userEvent.setup();
        const share = vi.fn().mockResolvedValue(undefined);
        Object.defineProperty(navigator, 'share', { value: share, configurable: true });
        vi.stubGlobal('fetch', vi.fn().mockResolvedValue({ blob: async () => new Blob(['x']) }));
        render(<PostcardShare url={URL} imageUrl={IMAGE} />);

        await user.click(screen.getByRole('button', { name: 'Compartilhar postal' }));

        expect((share.mock.lastCall?.[0] as ShareData).url).toBe(URL);
        vi.unstubAllGlobals();
    });

    it('always lets people download the image', () => {
        render(<PostcardShare url={URL} imageUrl={IMAGE} />);

        const download = screen.getByRole('link', { name: 'Baixar postal' });
        expect(download.getAttribute('href')).toBe(IMAGE);
        expect(download.getAttribute('download')).toBe('postal-ovniporto.jpg');
    });
});

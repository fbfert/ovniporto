import { afterEach, describe, expect, it, vi } from 'vitest';
import { track } from './analytics';

afterEach(() => {
    delete window.umami;
});

describe('track', () => {
    it('does nothing where the metric script is not loaded', () => {
        expect(() => track('avise_me')).not.toThrow();
    });

    it('sends the event to Umami when the script is there', () => {
        const spy = vi.fn();
        window.umami = { track: spy };

        track('adicionar_carrinho', { produto: 'adesivo-ovniporto' });

        expect(spy).toHaveBeenCalledWith('adicionar_carrinho', { produto: 'adesivo-ovniporto' });
    });

    it('never lets a failing script break the page', () => {
        window.umami = {
            track: () => {
                throw new Error('offline');
            },
        };

        expect(() => track('compra_concluida')).not.toThrow();
    });
});

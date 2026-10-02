import { cleanup, render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { afterEach, describe, expect, it, vi } from 'vitest';
import { ShippingQuote } from './ShippingQuote';

afterEach(cleanup);

describe('ShippingQuote', () => {
    it('shows an error for a short CEP and never asks for a quote', async () => {
        const user = userEvent.setup();
        const request = vi.fn();
        render(<ShippingQuote request={request} />);

        await user.type(screen.getByRole('textbox', { name: 'CEP' }), '88501');
        await user.click(screen.getByRole('button', { name: 'Calcular' }));

        expect(request).not.toHaveBeenCalled();
        expect(screen.getByText('Digite um CEP com 8 números.')).toBeTruthy();
    });

    it('lists the options with price and days for a valid CEP', async () => {
        const user = userEvent.setup();
        const request = vi.fn().mockResolvedValue({
            cep: '88501-000',
            simulated: true,
            options: [{ id: 'pac', carrier: 'Correios', service: 'PAC', priceCents: 2190, days: 8 }],
        });
        render(<ShippingQuote request={request} />);

        await user.type(screen.getByRole('textbox', { name: 'CEP' }), '88501000');
        expect((screen.getByRole('textbox', { name: 'CEP' }) as HTMLInputElement).value).toBe('88501-000');
        await user.click(screen.getByRole('button', { name: 'Calcular' }));

        expect(request).toHaveBeenCalledWith('88501-000');
        expect(await screen.findByText('PAC')).toBeTruthy();
        expect(screen.getByText(/8 dias úteis/)).toBeTruthy();
        expect(screen.getByText(/R\$\s21,90/)).toBeTruthy();
        expect(screen.getByText(/Valores simulados/)).toBeTruthy();
    });
});

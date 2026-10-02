import { cleanup, render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { afterEach, describe, expect, it } from 'vitest';
import { Accordion } from './Accordion';

afterEach(cleanup);

const items = [
    { title: 'O OVNIPORTO já existe?', content: 'Ainda não.' },
    { title: 'É de graça?', content: 'A vigília vai ser grátis.' },
];

describe('Accordion', () => {
    it('starts closed with every answer out of reach', () => {
        render(<Accordion items={items} />);

        for (const button of screen.getAllByRole('button')) {
            expect(button.getAttribute('aria-expanded')).toBe('false');
            // jsdom has no inert property, only the attribute
            expect(document.getElementById(button.getAttribute('aria-controls')!)?.hasAttribute('inert')).toBe(true);
        }
    });

    it('keeps at most one answer open', async () => {
        const user = userEvent.setup();
        render(<Accordion items={items} />);
        const [first, second] = screen.getAllByRole('button');

        await user.click(first!);
        expect(first!.getAttribute('aria-expanded')).toBe('true');

        await user.click(second!);
        expect(first!.getAttribute('aria-expanded')).toBe('false');
        expect(second!.getAttribute('aria-expanded')).toBe('true');
        expect(screen.getByRole('region', { name: 'É de graça?' }).hasAttribute('inert')).toBe(false);
    });

    it('closes the open answer when its question is pressed again', async () => {
        const user = userEvent.setup();
        render(<Accordion items={items} />);
        const [first] = screen.getAllByRole('button');

        await user.click(first!);
        await user.click(first!);

        expect(first!.getAttribute('aria-expanded')).toBe('false');
    });
});

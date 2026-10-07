import { cleanup, render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { afterEach, describe, expect, it, vi } from 'vitest';
import { MindMap, type MapNode } from './MindMap';

afterEach(cleanup);

const ROOT: MapNode = {
    label: 'Relatos',
    children: [
        {
            label: 'Fila',
            kind: 'screen',
            section: 'fila',
            children: [
                { label: 'Aprovar', kind: 'action', section: 'aprovar' },
                { label: 'Rejeitar', kind: 'action', section: 'rejeitar' },
            ],
        },
        { label: 'Cuidados', kind: 'care', section: 'cuidados' },
    ],
};

function setup() {
    const onActivate = vi.fn();
    render(<MindMap root={ROOT} label="Mapa de relatos" onActivate={onActivate} />);
    return { onActivate, user: userEvent.setup() };
}

describe('MindMap', () => {
    it('is one named tree with every node as an item at its level', () => {
        setup();

        expect(screen.getByRole('tree', { name: 'Mapa de relatos' })).toBeTruthy();
        const items = screen.getAllByRole('treeitem');
        expect(items.map((i) => [i.textContent, i.getAttribute('aria-level')])).toEqual([
            ['Relatos', '1'],
            ['Fila', '2'],
            ['Aprovar', '3'],
            ['Rejeitar', '3'],
            ['Cuidados', '2'],
        ]);
        expect(screen.getByRole('treeitem', { name: 'Aprovar' }).getAttribute('aria-posinset')).toBe('1');
        expect(screen.getByRole('treeitem', { name: 'Fila' }).getAttribute('aria-expanded')).toBe('true');
    });

    it('keeps a single tab stop and moves with the arrows', async () => {
        const { user } = setup();

        await user.tab();
        expect(document.activeElement?.textContent).toBe('Relatos');
        await user.keyboard('{ArrowRight}');
        expect(document.activeElement?.textContent).toBe('Fila');
        await user.keyboard('{ArrowRight}{ArrowDown}');
        expect(document.activeElement?.textContent).toBe('Rejeitar');
        await user.keyboard('{ArrowLeft}');
        expect(document.activeElement?.textContent).toBe('Fila');
        await user.keyboard('{End}');
        expect(document.activeElement?.textContent).toBe('Cuidados');
        expect(screen.getAllByRole('treeitem').filter((i) => i.tabIndex === 0)).toHaveLength(1);
    });

    it('opens the section of the focused node with Enter or a click, and ignores the centre', async () => {
        const { user, onActivate } = setup();

        await user.tab();
        await user.keyboard('{Enter}');
        expect(onActivate).not.toHaveBeenCalled();

        await user.keyboard('{ArrowDown}{ArrowDown}{Enter}');
        expect(onActivate).toHaveBeenLastCalledWith(expect.objectContaining({ section: 'aprovar' }));

        await user.click(screen.getByRole('treeitem', { name: 'Cuidados' }));
        expect(onActivate).toHaveBeenLastCalledWith(expect.objectContaining({ section: 'cuidados' }));
    });
});

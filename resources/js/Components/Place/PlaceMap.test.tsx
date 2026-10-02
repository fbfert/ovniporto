import { cleanup, render, screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { afterEach, describe, expect, it, vi } from 'vitest';
import { PlaceMap } from './PlaceMap';

const HANDLERS = ['dragging', 'touchZoom', 'doubleClickZoom', 'scrollWheelZoom', 'boxZoom', 'keyboard'] as const;

const handlers = Object.fromEntries(HANDLERS.map((name) => [name, { enable: vi.fn(), disable: vi.fn() }]));
const chain = { addTo: vi.fn().mockReturnThis() };

vi.mock('leaflet', () => ({
    default: {
        map: vi.fn(() => ({ ...handlers, remove: vi.fn() })),
        tileLayer: vi.fn(() => chain),
        marker: vi.fn(() => chain),
        divIcon: vi.fn(),
        control: { zoom: vi.fn(() => chain) },
    },
}));
vi.mock('leaflet/dist/leaflet.css', () => ({}));

afterEach(cleanup);

describe('PlaceMap', () => {
    it('stays inert until the visitor asks to move it', async () => {
        const user = userEvent.setup();
        render(<PlaceMap />);

        const activate = await screen.findByRole('button', { name: 'Mexer no mapa' });
        for (const name of HANDLERS) {
            expect(handlers[name]!.disable).toHaveBeenCalled();
            expect(handlers[name]!.enable).not.toHaveBeenCalled();
        }

        await user.click(activate);

        for (const name of HANDLERS) expect(handlers[name]!.enable).toHaveBeenCalled();
        await waitFor(() => expect(screen.queryByRole('button', { name: 'Mexer no mapa' })).toBeNull());
    });
});

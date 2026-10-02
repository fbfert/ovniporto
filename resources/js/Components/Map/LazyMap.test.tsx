import { cleanup, render, screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { LazyMap, ovniportoMarker } from './LazyMap';

const HANDLERS = ['dragging', 'touchZoom', 'doubleClickZoom', 'scrollWheelZoom', 'boxZoom', 'keyboard'] as const;

const leaflet = vi.hoisted(() => {
    const chain = () => ({ addTo: vi.fn().mockReturnThis(), bindPopup: vi.fn().mockReturnThis() });
    return {
        handlers: {} as Record<string, { enable: ReturnType<typeof vi.fn>; disable: ReturnType<typeof vi.fn> }>,
        marker: vi.fn((latLng: [number, number], options: { zIndexOffset: number; title: string }) => ({
            ...chain(),
            latLng,
            options,
        })),
        chain,
    };
});

vi.mock('leaflet', () => ({
    default: {
        map: vi.fn(() => ({ ...leaflet.handlers, remove: vi.fn(), fitBounds: vi.fn() })),
        tileLayer: vi.fn(() => leaflet.chain()),
        layerGroup: vi.fn(() => ({ addTo: vi.fn().mockReturnThis(), clearLayers: vi.fn() })),
        marker: leaflet.marker,
        divIcon: vi.fn(),
        latLngBounds: vi.fn(),
        control: { zoom: vi.fn(() => leaflet.chain()) },
    },
}));
vi.mock('leaflet/dist/leaflet.css', () => ({}));

beforeEach(() => {
    for (const name of HANDLERS) leaflet.handlers[name] = { enable: vi.fn(), disable: vi.fn() };
    leaflet.marker.mockClear();
});
afterEach(cleanup);

describe('LazyMap', () => {
    it('stays inert until the visitor asks to move it', async () => {
        const user = userEvent.setup();
        render(<LazyMap markers={[ovniportoMarker()]} label="Mapa" />);

        const activate = await screen.findByRole('button', { name: 'Mexer no mapa' });
        for (const name of HANDLERS) {
            expect(leaflet.handlers[name]!.disable).toHaveBeenCalled();
            expect(leaflet.handlers[name]!.enable).not.toHaveBeenCalled();
        }

        await user.click(activate);

        for (const name of HANDLERS) expect(leaflet.handlers[name]!.enable).toHaveBeenCalled();
        await waitFor(() => expect(screen.queryByRole('button', { name: 'Mexer no mapa' })).toBeNull());
    });

    it('draws one marker per partner plus the OVNIPORTO on top', async () => {
        render(
            <LazyMap
                label="Mapa"
                markers={[
                    ovniportoMarker(),
                    { id: 'a', lat: -27.8, lng: -50.3, label: 'Pousada', href: '/regiao/pousada' },
                    { id: 'b', lat: -27.9, lng: -50.1, label: 'Trilha', href: '/regiao/trilha' },
                ]}
            />,
        );

        await waitFor(() => expect(leaflet.marker).toHaveBeenCalledTimes(3));
        const titles = leaflet.marker.mock.calls.map(([, options]) => options.title);
        expect(titles).toEqual(['OVNIPORTO (planejado)', 'Pousada', 'Trilha']);
        expect(leaflet.marker.mock.calls[0]![1].zIndexOffset).toBe(1000);
    });
});

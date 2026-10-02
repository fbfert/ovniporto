import type { Map as LeafletMap } from 'leaflet';
import { useEffect, useRef, useState } from 'react';
import { PinIcon } from '@/Components/Icons';
import { t } from '@/i18n/pt-BR';

export const OVNIPORTO_COORDS: [number, number] = [-27.85495, -50.21841];

const copy = t.placePage;

/** The marker: a small saucer with its green beam, drawn in the brand colors. */
const SAUCER_MARKER = `
<svg viewBox="0 0 48 48" width="44" height="44" aria-hidden="true">
  <path d="M18 24 L30 24 L36 44 L12 44 Z" fill="#54C933" opacity=".35"/>
  <ellipse cx="24" cy="24" rx="16" ry="5" fill="#F4F5E8" stroke="#061121" stroke-width="2"/>
  <path d="M15 22c1-6 17-6 18 0z" fill="#ADDBA1" stroke="#061121" stroke-width="2"/>
  <circle cx="17" cy="24.5" r="1.4" fill="#54C933"/><circle cx="24" cy="25.5" r="1.4" fill="#54C933"/><circle cx="31" cy="24.5" r="1.4" fill="#54C933"/>
</svg>`;

const INTERACTIONS = ['dragging', 'touchZoom', 'doubleClickZoom', 'scrollWheelZoom', 'boxZoom', 'keyboard'] as const;

/**
 * OpenStreetMap centred on the future runway. Leaflet is imported only in the
 * browser, after mount (it touches window on import, and most visitors never
 * scroll this far). The map is inert until the visitor asks to move it, so it
 * never steals the page scroll on a phone.
 */
export function PlaceMap({ className = '' }: { className?: string }) {
    const containerRef = useRef<HTMLDivElement>(null);
    const mapRef = useRef<LeafletMap | null>(null);
    const [ready, setReady] = useState(false);
    const [active, setActive] = useState(false);

    useEffect(() => {
        let cancelled = false;
        (async () => {
            const L = (await import('leaflet')).default;
            await import('leaflet/dist/leaflet.css');
            if (cancelled || !containerRef.current) return;

            const map = L.map(containerRef.current, {
                center: OVNIPORTO_COORDS,
                zoom: 14,
                zoomControl: false,
                attributionControl: true,
            });
            INTERACTIONS.forEach((handler) => map[handler].disable());
            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 18,
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
            }).addTo(map);
            L.marker(OVNIPORTO_COORDS, {
                icon: L.divIcon({ html: SAUCER_MARKER, className: '', iconSize: [44, 44], iconAnchor: [22, 40] }),
                title: copy.markerLabel,
                alt: copy.markerLabel,
            }).addTo(map);

            mapRef.current = map;
            setReady(true);
        })();

        return () => {
            cancelled = true;
            mapRef.current?.remove();
            mapRef.current = null;
        };
    }, []);

    const activate = () => {
        const map = mapRef.current;
        if (!map) return;
        INTERACTIONS.forEach((handler) => map[handler].enable());
        import('leaflet').then(({ default: L }) => L.control.zoom({ position: 'topright' }).addTo(map));
        setActive(true);
        containerRef.current?.focus();
    };

    return (
        <div
            className={`relative isolate overflow-hidden rounded-[22px] bg-night-blue ring-1 ring-night/10 ${className}`}
        >
            <div
                ref={containerRef}
                role="region"
                aria-label={copy.mapLabel}
                tabIndex={active ? 0 : -1}
                className="absolute inset-0 z-0"
            />
            {!active && (
                <button
                    type="button"
                    onClick={activate}
                    disabled={!ready}
                    className="absolute inset-0 z-[500] flex items-end justify-center bg-night/10 p-5 transition-colors duration-200 hover:bg-night/20"
                >
                    <span className="inline-flex min-h-11 press items-center gap-2 rounded-full bg-moonlight px-5 text-sm font-semibold text-night shadow-lift">
                        <PinIcon size="1.05rem" />
                        {ready ? copy.mapActivate : copy.mapLoading}
                    </span>
                </button>
            )}
        </div>
    );
}

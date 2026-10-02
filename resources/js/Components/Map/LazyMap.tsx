import type { LayerGroup, Map as LeafletMap } from 'leaflet';
import { useEffect, useRef, useState } from 'react';
import { PinIcon } from '@/Components/Icons';
import { t } from '@/i18n/pt-BR';

export const OVNIPORTO_COORDS: [number, number] = [-27.85495, -50.21841];

export interface MapMarker {
    id: string;
    lat: number;
    lng: number;
    label: string;
    /** Opens from the marker's popup. */
    href?: string;
    /** The OVNIPORTO itself: drawn as the saucer, always on top. */
    highlight?: boolean;
}

const copy = t.placePage;

/** The OVNIPORTO marker: a small saucer with its green beam, in the brand colors. */
const SAUCER_MARKER = `
<svg viewBox="0 0 48 48" width="44" height="44" aria-hidden="true">
  <path d="M18 24 L30 24 L36 44 L12 44 Z" fill="#54C933" opacity=".35"/>
  <ellipse cx="24" cy="24" rx="16" ry="5" fill="#F4F5E8" stroke="#061121" stroke-width="2"/>
  <path d="M15 22c1-6 17-6 18 0z" fill="#ADDBA1" stroke="#061121" stroke-width="2"/>
  <circle cx="17" cy="24.5" r="1.4" fill="#54C933"/><circle cx="24" cy="25.5" r="1.4" fill="#54C933"/><circle cx="31" cy="24.5" r="1.4" fill="#54C933"/>
</svg>`;

/** Partners: a night-blue dot with a moonlight ring, quieter than the saucer. */
const PARTNER_MARKER =
    '<span style="display:block;width:18px;height:18px;border-radius:9999px;background:#142644;border:3px solid #F4F5E8;box-shadow:0 2px 6px rgb(6 17 33 / .45)"></span>';

const INTERACTIONS = ['dragging', 'touchZoom', 'doubleClickZoom', 'scrollWheelZoom', 'boxZoom', 'keyboard'] as const;

const escapeHtml = (text: string) =>
    text.replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c]!);

/**
 * OpenStreetMap with our markers. Leaflet is imported only in the browser,
 * after mount (it touches window on import). The map is inert until the
 * visitor asks to move it, so it never steals the page scroll on a phone.
 * Markers follow the `markers` prop (e.g. a filtered list) and the view fits them.
 */
export function LazyMap({
    markers,
    label,
    zoom = 14,
    className = '',
}: {
    markers: MapMarker[];
    label: string;
    zoom?: number;
    className?: string;
}) {
    const containerRef = useRef<HTMLDivElement>(null);
    const mapRef = useRef<LeafletMap | null>(null);
    const layerRef = useRef<LayerGroup | null>(null);
    const [ready, setReady] = useState(false);
    const [active, setActive] = useState(false);

    useEffect(() => {
        let cancelled = false;
        (async () => {
            const L = (await import('leaflet')).default;
            await import('leaflet/dist/leaflet.css');
            if (cancelled || !containerRef.current) return;

            const map = L.map(containerRef.current, { center: OVNIPORTO_COORDS, zoom, zoomControl: false });
            INTERACTIONS.forEach((handler) => map[handler].disable());
            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 18,
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
            }).addTo(map);
            layerRef.current = L.layerGroup().addTo(map);
            mapRef.current = map;
            setReady(true);
        })();

        return () => {
            cancelled = true;
            mapRef.current?.remove();
            mapRef.current = null;
        };
    }, [zoom]);

    useEffect(() => {
        const map = mapRef.current;
        const layer = layerRef.current;
        if (!ready || !map || !layer) return;
        let cancelled = false;
        import('leaflet').then(({ default: L }) => {
            if (cancelled) return;
            layer.clearLayers();
            for (const marker of markers) {
                const icon = marker.highlight
                    ? L.divIcon({ html: SAUCER_MARKER, className: '', iconSize: [44, 44], iconAnchor: [22, 40] })
                    : L.divIcon({ html: PARTNER_MARKER, className: '', iconSize: [18, 18], iconAnchor: [9, 9] });
                const pin = L.marker([marker.lat, marker.lng], {
                    icon,
                    title: marker.label,
                    alt: marker.label,
                    zIndexOffset: marker.highlight ? 1000 : 0,
                });
                if (marker.href) {
                    pin.bindPopup(`<a href="${escapeHtml(marker.href)}">${escapeHtml(marker.label)}</a>`);
                }
                pin.addTo(layer);
            }
            if (markers.length > 1) {
                map.fitBounds(L.latLngBounds(markers.map((m) => [m.lat, m.lng] as [number, number])), {
                    padding: [40, 40],
                    maxZoom: zoom,
                });
            }
        });
        return () => {
            cancelled = true;
        };
    }, [ready, markers, zoom]);

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
                aria-label={label}
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

/** The OVNIPORTO's own marker, for any map that needs it. */
export const ovniportoMarker = (): MapMarker => ({
    id: 'ovniporto',
    lat: OVNIPORTO_COORDS[0],
    lng: OVNIPORTO_COORDS[1],
    label: copy.markerLabel,
    highlight: true,
});

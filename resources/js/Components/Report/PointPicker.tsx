import type { Map as LeafletMap, Marker } from 'leaflet';
import { useEffect, useId, useRef, useState } from 'react';
import type { LatLng } from './draft';

const PIN = `<svg viewBox="0 0 36 48" width="36" height="48" aria-hidden="true">
  <path d="M18 46s14-15.6 14-27A14 14 0 0 0 4 19c0 11.4 14 27 14 27z" fill="#54C933" stroke="#061121" stroke-width="2.5"/>
  <circle cx="18" cy="19" r="5.5" fill="#061121"/>
</svg>`;

/**
 * Full-width map inside step 3: tap to place the point, drag to move it.
 * Unlike the public maps it is live at once, because marking is the job here.
 */
export function PointPicker({
    point,
    center,
    onChange,
    label,
    keyboardHint,
    className = '',
}: {
    point: LatLng | null;
    center: LatLng;
    onChange: (point: LatLng) => void;
    label: string;
    /** How to mark without a pointer; read by screen readers when the map takes focus. */
    keyboardHint: string;
    className?: string;
}) {
    const hintId = useId();
    const containerRef = useRef<HTMLDivElement>(null);
    const mapRef = useRef<LeafletMap | null>(null);
    const markerRef = useRef<Marker | null>(null);
    const onChangeRef = useRef(onChange);
    const [ready, setReady] = useState(false);

    useEffect(() => {
        onChangeRef.current = onChange;
    }, [onChange]);

    useEffect(() => {
        let cancelled = false;
        (async () => {
            const L = (await import('leaflet')).default;
            await import('leaflet/dist/leaflet.css');
            if (cancelled || !containerRef.current) return;
            const map = L.map(containerRef.current, { center: [center.lat, center.lng], zoom: point ? 14 : 11 });
            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 18,
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
            }).addTo(map);
            map.on('click', (event) => onChangeRef.current({ lat: event.latlng.lat, lng: event.latlng.lng }));
            // Keyboard: the arrows pan (Leaflet), Enter or Space marks the center, under the crosshair.
            containerRef.current.addEventListener('keydown', (event) => {
                if (event.target !== containerRef.current || (event.key !== 'Enter' && event.key !== ' ')) return;
                event.preventDefault();
                const center = map.getCenter();
                onChangeRef.current({ lat: center.lat, lng: center.lng });
            });
            mapRef.current = map;
            setReady(true);
        })();
        return () => {
            cancelled = true;
            mapRef.current?.remove();
            mapRef.current = null;
            markerRef.current = null;
        };
        // the map is created once; later centers come through the effect below
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    useEffect(() => {
        const map = mapRef.current;
        if (!ready || !map) return;
        import('leaflet').then(({ default: L }) => {
            if (!point) {
                markerRef.current?.remove();
                markerRef.current = null;
                return;
            }
            if (!markerRef.current) {
                markerRef.current = L.marker([point.lat, point.lng], {
                    draggable: true,
                    icon: L.divIcon({ html: PIN, className: '', iconSize: [36, 48], iconAnchor: [18, 46] }),
                    keyboard: true,
                    title: label,
                }).addTo(map);
                markerRef.current.on('dragend', () => {
                    const at = markerRef.current?.getLatLng();
                    if (at) onChangeRef.current({ lat: at.lat, lng: at.lng });
                });
            } else {
                markerRef.current.setLatLng([point.lat, point.lng]);
            }
            if (!map.getBounds().contains([point.lat, point.lng])) map.setView([point.lat, point.lng], 14);
        });
    }, [ready, point, label]);

    useEffect(() => {
        if (ready && !point) mapRef.current?.setView([center.lat, center.lng]);
    }, [ready, center, point]);

    return (
        <div className={`relative ${className}`}>
            <div
                ref={containerRef}
                role="region"
                aria-label={label}
                aria-describedby={hintId}
                className="peer isolate h-full w-full overflow-hidden rounded-[inherit] bg-night-blue ring-1 ring-moonlight/15"
            />
            {/* Crosshair while the map has keyboard focus: Enter marks this spot. */}
            <svg
                aria-hidden
                viewBox="0 0 40 40"
                className="pointer-events-none absolute top-1/2 left-1/2 z-[500] hidden size-10 -translate-x-1/2 -translate-y-1/2 text-beam peer-focus-visible:block"
            >
                <circle cx="20" cy="20" r="9" fill="none" stroke="currentColor" strokeWidth="2.5" />
                <path d="M20 2v10M20 28v10M2 20h10M28 20h10" stroke="currentColor" strokeWidth="2.5" />
            </svg>
            <p id={hintId} className="sr-only">
                {keyboardHint}
            </p>
        </div>
    );
}

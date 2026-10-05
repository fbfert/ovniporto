import type { LayerGroup, Map as LeafletMap } from 'leaflet';
import { useEffect, useRef, useState } from 'react';
import { OVNIPORTO_COORDS } from '@/Components/Map/LazyMap';
import { shortDate, t } from '@/i18n/pt-BR';
import type { HistoricalPin } from '@/types/historical';

export interface SightingPin {
    id: number;
    type: string;
    lat: number;
    lng: number;
    date: string;
    nickname: string;
    thumb: string | null;
}

const escapeHtml = (text: string) =>
    text.replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c]!);

/** The popup is a mini polaroid: photo (or a drawn sky), type, date, nickname and the link. */
function popupHtml(pin: SightingPin): string {
    const type = escapeHtml(t.logbook.types[pin.type as keyof typeof t.logbook.types] ?? pin.type);
    const art = pin.thumb
        ? `<img src="${escapeHtml(pin.thumb)}" alt="" style="width:100%;aspect-ratio:1;object-fit:cover;display:block;background:#061121" />`
        : `<div style="width:100%;aspect-ratio:1;background:radial-gradient(90% 70% at 30% 10%,#142644,#061121);position:relative"><span style="position:absolute;left:38%;top:30%;width:10px;height:10px;border-radius:99px;background:#ADDBA1;box-shadow:0 0 14px 4px rgb(84 201 51 / .55)"></span></div>`;
    return `${art}
<p style="margin:8px 0 0;font-family:Caveat,cursive;font-size:20px;line-height:1.1;text-align:center">${type} · ${escapeHtml(shortDate(pin.date))}</p>
<p style="margin:2px 0 6px;font-size:12px;text-align:center;opacity:.7">${escapeHtml(t.logbookPage.by(pin.nickname))}</p>
<a href="/relatos/${pin.id}" style="display:block;margin:0 0 6px;padding:8px 0;border-radius:999px;background:#54C933;color:#061121;font-weight:600;font-size:13px;text-align:center;text-decoration:none">${escapeHtml(t.logbookPage.seeReport)}</a>`;
}

/** A historical case: title, date and the link to its page; no photo, it is not a member's report. */
function historicalPopupHtml(pin: HistoricalPin): string {
    return `<p style="margin:4px 0 0;font-family:Caveat,cursive;font-size:18px;line-height:1.1;text-align:center;color:#494383">${escapeHtml(pin.date)}</p>
<p style="margin:4px 0 8px;font-weight:600;font-size:13px;line-height:1.3;text-align:center">${escapeHtml(pin.title)}</p>
<a href="/mapa/casos/${escapeHtml(pin.slug)}" style="display:block;margin:0 0 6px;padding:8px 0;border-radius:999px;background:#494383;color:#F4F5E8;font-weight:600;font-size:13px;text-align:center;text-decoration:none">${escapeHtml(t.historical.seeCase)}</a>`;
}

/**
 * Full-width night map of the approved reports, clustered. Leaflet and the
 * cluster plugin load in the browser only. Pins follow the filters (prop). Historical cases sit on
 * their own layer (lilac diamonds): never clustered with the reports and never used to frame the
 * map, so the view stays on the serra while the world's cases wait for a zoom out.
 */
export function SightingsMap({
    pins,
    historical = [],
    className = '',
}: {
    pins: SightingPin[];
    historical?: HistoricalPin[];
    className?: string;
}) {
    const containerRef = useRef<HTMLDivElement>(null);
    const mapRef = useRef<LeafletMap | null>(null);
    const clusterRef = useRef<LayerGroup | null>(null);
    const historicalRef = useRef<LayerGroup | null>(null);
    const [ready, setReady] = useState(false);

    useEffect(() => {
        let cancelled = false;
        (async () => {
            const L = (await import('leaflet')).default;
            await Promise.all([
                import('leaflet/dist/leaflet.css'),
                import('leaflet.markercluster'),
                import('leaflet.markercluster/dist/MarkerCluster.css'),
            ]);
            if (cancelled || !containerRef.current) return;
            const map = L.map(containerRef.current, { center: OVNIPORTO_COORDS, zoom: 9, scrollWheelZoom: false });
            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 17,
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
            }).addTo(map);
            clusterRef.current = L.markerClusterGroup({
                showCoverageOnHover: false,
                maxClusterRadius: 50,
                iconCreateFunction: (cluster) =>
                    L.divIcon({
                        html: `<span class="sighting-cluster">${cluster.getChildCount()}</span>`,
                        className: '',
                        iconSize: [44, 44],
                    }),
            }).addTo(map);
            historicalRef.current = L.layerGroup().addTo(map);
            mapRef.current = map;
            setReady(true);
        })();
        return () => {
            cancelled = true;
            mapRef.current?.remove();
            mapRef.current = null;
        };
    }, []);

    useEffect(() => {
        const map = mapRef.current;
        const cluster = clusterRef.current;
        if (!ready || !map || !cluster) return;
        let cancelled = false;
        import('leaflet').then(({ default: L }) => {
            if (cancelled) return;
            cluster.clearLayers();
            for (const pin of pins) {
                L.marker([pin.lat, pin.lng], {
                    icon: L.divIcon({ html: '<span class="sighting-dot"></span>', className: '', iconSize: [14, 14] }),
                    title: `${t.logbook.types[pin.type as keyof typeof t.logbook.types] ?? pin.type} · ${pin.date}`,
                    alt: pin.nickname,
                })
                    .bindPopup(popupHtml(pin), { closeButton: true, maxWidth: 200 })
                    .addTo(cluster);
            }
            if (pins.length > 0) {
                map.fitBounds(L.latLngBounds(pins.map((p) => [p.lat, p.lng] as [number, number])), {
                    padding: [40, 40],
                    maxZoom: 11,
                });
            }
        });
        return () => {
            cancelled = true;
        };
    }, [ready, pins]);

    useEffect(() => {
        const layer = historicalRef.current;
        if (!ready || !layer) return;
        let cancelled = false;
        import('leaflet').then(({ default: L }) => {
            if (cancelled) return;
            layer.clearLayers();
            for (const pin of historical) {
                L.marker([pin.lat, pin.lng], {
                    icon: L.divIcon({
                        html: '<span class="historical-dot"></span>',
                        className: '',
                        iconSize: [14, 14],
                    }),
                    title: `${pin.title} · ${pin.date}`,
                    zIndexOffset: -100,
                })
                    .bindPopup(historicalPopupHtml(pin), { closeButton: true, maxWidth: 200 })
                    .addTo(layer);
            }
        });
        return () => {
            cancelled = true;
        };
    }, [ready, historical]);

    return (
        <div
            ref={containerRef}
            role="region"
            aria-label={t.logbookPage.mapLabel}
            className={`night-map isolate bg-night ${className}`}
        />
    );
}

import { LazyMap, ovniportoMarker } from '@/Components/Map/LazyMap';
import { t } from '@/i18n/pt-BR';

export { OVNIPORTO_COORDS } from '@/Components/Map/LazyMap';

const MARKERS = [ovniportoMarker()];

/** Where the runway will be: the OVNIPORTO marker alone. */
export function PlaceMap({ className = '' }: { className?: string }) {
    return <LazyMap markers={MARKERS} label={t.placePage.mapLabel} className={className} />;
}

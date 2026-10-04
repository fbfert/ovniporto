import { NightSkyArt, Polaroid } from '@/Components/Ui/Polaroid';
import { shortDate, t } from '@/i18n/pt-BR';
import type { SightingCard } from '@/types';

/** One approved report as an instant photo: its first photo, or a drawn sky with the type. */
export function SightingPolaroid({
    sighting,
    rotate = 0,
    tape = 'top',
    className = '',
}: {
    sighting: SightingCard;
    rotate?: number;
    tape?: 'top' | 'corner' | 'none';
    className?: string;
}) {
    const type = t.logbook.types[sighting.type];
    return (
        <Polaroid
            href={`/relatos/${sighting.id}`}
            rotate={rotate}
            tape={tape}
            src={sighting.photo}
            sources={sighting.photoSources}
            alt={t.logbook.photoAlt(sighting.type, sighting.nickname)}
            art={<NightSkyArt label={type} seed={sighting.id} />}
            caption={`${type} · ${sighting.place ?? t.logbook.noPlace} · ${shortDate(sighting.date)}`}
            className={className}
        />
    );
}

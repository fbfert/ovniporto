import { Link } from '@inertiajs/react';
import { PinIcon } from '@/Components/Icons';
import { NightSkyArt } from '@/Components/Ui/Polaroid';
import { Badge } from '@/Components/Ui/Typography';
import { t } from '@/i18n/pt-BR';

export interface PartnerListing {
    name: string;
    slug: string;
    type: string;
    city: string;
    shortDescription: string | null;
    cover: string | null;
    lat: number | null;
    lng: number | null;
    isExample: boolean;
    distanceKm: number | null;
}

/** Square photo, then name, type, city and the straight-line distance (labelled "aprox."). */
export function PartnerTile({ partner }: { partner: PartnerListing }) {
    return (
        <Link href={`/regiao/${partner.slug}`} className="group block">
            <div className="relative aspect-square overflow-hidden rounded-[22px] bg-night">
                {partner.cover ? (
                    <img
                        src={partner.cover}
                        alt=""
                        loading="lazy"
                        className="h-full w-full object-cover transition-transform duration-500 ease-snap [@media(hover:hover)]:group-hover:scale-[1.04]"
                    />
                ) : (
                    <NightSkyArt label={t.regionPage.coverPending} seed={partner.slug.length} />
                )}
                <div className="absolute top-3 left-3 flex flex-wrap gap-1.5">
                    <Badge tone="horizon">{t.region.types[partner.type] ?? partner.type}</Badge>
                    {partner.isExample && <Badge tone="car">{t.region.example}</Badge>}
                </div>
            </div>
            <h2 className="mt-4 font-display text-lg leading-tight font-bold tracking-[0.03em] uppercase group-hover:underline">
                {partner.name}
            </h2>
            <p className="mt-1.5 flex flex-wrap items-center gap-x-2 text-sm text-night/65">
                <PinIcon size="0.95rem" className="text-horizon" />
                {partner.city}
                {partner.distanceKm !== null && <span>· {t.regionPage.distance(partner.distanceKm)}</span>}
            </p>
        </Link>
    );
}

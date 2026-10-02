import type { ReactNode } from 'react';
import { InstagramIcon, PinIcon, WhatsAppIcon } from '@/Components/Icons';
import { SeoHead } from '@/Components/Layout/SeoHead';
import { LazyMap, ovniportoMarker } from '@/Components/Map/LazyMap';
import type { PartnerListing } from '@/Components/Region/PartnerTile';
import { Button } from '@/Components/Ui/Button';
import { NightSkyArt } from '@/Components/Ui/Polaroid';
import { Section } from '@/Components/Ui/Section';
import { Badge, Display, Eyebrow } from '@/Components/Ui/Typography';
import { t } from '@/i18n/pt-BR';
import { PublicLayout } from '@/Layouts/PublicLayout';

const copy = t.regionPage;

interface Partner extends PartnerListing {
    address: string | null;
    phone: string | null;
    whatsapp: string | null;
    instagram: string | null;
    website: string | null;
    gallery: { url: string; alt: string }[];
}

const digits = (value: string) => value.replace(/\D/g, '');

/** Brazilian numbers without country code get +55. */
const whatsappUrl = (number: string) => {
    const d = digits(number);
    return `https://wa.me/${d.length <= 11 ? `55${d}` : d}`;
};

const instagramUrl = (handle: string) =>
    handle.startsWith('http') ? handle : `https://instagram.com/${handle.replace(/^@/, '')}`;

/** Google Maps route from the OVNIPORTO to the partner (coordinates, or the address as a fallback). */
const directionsUrl = (partner: Partner, origin: { lat: number; lng: number }) => {
    const destination =
        partner.lat !== null && partner.lng !== null
            ? `${partner.lat},${partner.lng}`
            : `${partner.address ?? partner.name}, ${partner.city}`;
    return `https://www.google.com/maps/dir/?api=1&origin=${origin.lat},${origin.lng}&destination=${encodeURIComponent(destination)}`;
};

interface ContactButton {
    href: string;
    label: string;
    icon?: ReactNode;
}

/** One button per contact the partner actually filled in; nothing for the empty ones. */
function contactButtons(partner: Partner): ContactButton[] {
    const buttons: ContactButton[] = [];
    if (partner.whatsapp)
        buttons.push({ href: whatsappUrl(partner.whatsapp), label: copy.whatsapp, icon: <WhatsAppIcon /> });
    if (partner.instagram)
        buttons.push({ href: instagramUrl(partner.instagram), label: copy.instagram, icon: <InstagramIcon /> });
    if (partner.website) buttons.push({ href: partner.website, label: copy.website });
    if (partner.phone) buttons.push({ href: `tel:+55${digits(partner.phone)}`, label: copy.call });
    return buttons;
}

function Contacts({ partner }: { partner: Partner }) {
    const buttons = contactButtons(partner);

    if (buttons.length === 0) return null;
    return (
        <div className="mt-10">
            <h2 className="text-[0.7rem] font-semibold tracking-[0.12em] text-night/60 uppercase">{copy.contacts}</h2>
            <ul className="mt-4 flex flex-wrap gap-3">
                {buttons.map((button) => (
                    <li key={button.label}>
                        <Button href={button.href} external variant="secondary" iconLeft={button.icon}>
                            {button.label}
                        </Button>
                    </li>
                ))}
            </ul>
        </div>
    );
}

export default function RegionShow({ partner, origin }: { partner: Partner; origin: { lat: number; lng: number } }) {
    const markers = [
        ovniportoMarker(),
        ...(partner.lat !== null && partner.lng !== null
            ? [{ id: partner.slug, lat: partner.lat, lng: partner.lng, label: partner.name }]
            : []),
    ];

    return (
        <>
            <SeoHead
                title={partner.name}
                description={partner.shortDescription ?? copy.description}
                image={partner.cover ?? undefined}
            />

            <Section tone="light" innerClassName="pt-32! sm:pt-36!">
                <div className="grid gap-10 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)] lg:gap-16">
                    <div className="relative aspect-[4/3] overflow-hidden rounded-[26px] bg-night lg:aspect-square">
                        {partner.cover ? (
                            <img src={partner.cover} alt={partner.name} className="h-full w-full object-cover" />
                        ) : (
                            <NightSkyArt label={copy.coverPending} seed={partner.slug.length} />
                        )}
                    </div>
                    <div className="lg:pt-6">
                        <Eyebrow>{copy.eyebrow}</Eyebrow>
                        <Display as="h1" className="mt-3 text-[clamp(1.9rem,1rem+3.6vw,3.6rem)]! text-balance">
                            {partner.name}
                        </Display>
                        <p className="mt-5 flex flex-wrap items-center gap-2 text-night/70">
                            <Badge tone="horizon">{t.region.types[partner.type] ?? partner.type}</Badge>
                            {partner.isExample && <Badge tone="car">{t.region.example}</Badge>}
                            <span className="inline-flex items-center gap-1.5">
                                <PinIcon size="1rem" className="text-horizon" />
                                {partner.address ? `${partner.address} · ` : ''}
                                {partner.city}
                            </span>
                        </p>
                        {partner.distanceKm !== null && (
                            <p className="mt-2 text-sm text-night/60">{copy.distance(partner.distanceKm)}</p>
                        )}
                        {partner.shortDescription && (
                            <p className="mt-8 max-w-[52ch] text-lg leading-relaxed text-night/80">
                                {partner.shortDescription}
                            </p>
                        )}
                        <Contacts partner={partner} />
                    </div>
                </div>

                {partner.gallery.length > 0 && (
                    <section aria-labelledby="fotos" className="mt-16">
                        <h2
                            id="fotos"
                            className="text-[0.7rem] font-semibold tracking-[0.12em] text-night/60 uppercase"
                        >
                            {copy.gallery}
                        </h2>
                        <ul className="mt-4 grid grid-cols-2 gap-3 md:grid-cols-4">
                            {partner.gallery.map((image) => (
                                <li key={image.url}>
                                    <img
                                        src={image.url}
                                        alt={image.alt}
                                        loading="lazy"
                                        className="aspect-square w-full rounded-[16px] object-cover"
                                    />
                                </li>
                            ))}
                        </ul>
                    </section>
                )}
            </Section>

            <Section tone="dusk" wave>
                <div className="grid gap-10 lg:grid-cols-[minmax(0,0.8fr)_minmax(0,1.2fr)] lg:items-center">
                    <div>
                        <Display as="h2" className="text-[clamp(1.5rem,1rem+2vw,2.4rem)]!">
                            {copy.directions}
                        </Display>
                        <p className="mt-3 max-w-[36ch] text-night/75">{copy.directionsHint}</p>
                        <div className="mt-6 flex flex-wrap gap-3">
                            <Button href={directionsUrl(partner, origin)} external>
                                {copy.directions}
                            </Button>
                            <Button href="/regiao" variant="secondary">
                                {copy.back}
                            </Button>
                        </div>
                    </div>
                    <LazyMap markers={markers} label={copy.mapLabel} zoom={11} className="aspect-[4/3] w-full" />
                </div>
            </Section>
        </>
    );
}

RegionShow.layout = (page: ReactNode) => <PublicLayout>{page}</PublicLayout>;

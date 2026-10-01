export type SightingTypeValue = 'light' | 'object' | 'trail' | 'other';

export interface SightingCard {
    id: number;
    type: SightingTypeValue;
    place: string | null;
    date: string;
    nickname: string;
    photo: string | null;
}

export interface ProductCard {
    id: number;
    name: string;
    slug: string;
    priceCents: number;
    comparePriceCents: number | null;
    label: string | null;
    madeToOrder: boolean;
    productionDays: number;
    image: string | null;
    imageAlt: string | null;
}

export interface PlaceSpace {
    slug: string;
    name: string;
    role: string;
    phase: number;
    status: 'planning' | 'building' | 'open';
}

export interface PartnerCard {
    name: string;
    slug: string;
    type: string;
    city: string;
    cover: string | null;
    isExample: boolean;
}

export interface HomeContent {
    home_intro: string;
    home_place: string;
    home_store: string;
    home_legend: string;
    legend_body: string | null;
    link_whatsapp: string;
    link_instagram: string;
    contact_email: string;
}

export interface HomeProps {
    counters: { members: number; sightings: number };
    content: HomeContent;
    sightings: SightingCard[];
    products: ProductCard[];
    spaces: PlaceSpace[];
    partners: PartnerCard[];
}

export interface SharedProps {
    appUrl: string;
    currentUrl: string;
    flash: { toast: string | null };
    errors: Record<string, string>;
    [key: string]: unknown;
}

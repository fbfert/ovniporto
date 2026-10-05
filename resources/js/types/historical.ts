/** A historical case of the Livro de avistamentos, researched by the team (resources/content/sightings). */
export interface HistoricalCaseCard {
    slug: string;
    title: string;
    date: string;
    place: string;
    summary: string;
    image: string;
}

export interface HistoricalCase extends HistoricalCaseCard {
    region: string;
    dateNote?: string;
    documentation: string;
    sources: { label: string; url: string }[];
    coordinates: { lat: number; lng: number; region?: boolean };
}

export interface HistoricalPin {
    slug: string;
    title: string;
    date: string;
    lat: number;
    lng: number;
}

export interface HistoricalSection {
    eyebrow: string;
    title: string;
    intro: string;
    note: string;
    groups: { region: string; label: string; cases: HistoricalCaseCard[] }[];
    pins: HistoricalPin[];
}

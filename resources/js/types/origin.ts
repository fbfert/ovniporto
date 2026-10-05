/** Data of the /origem pages, as the origin use cases deliver it (resources/content/origin). */

export interface ImageCredit {
    slug: string;
    title: string;
    alt: string;
    author: string;
    license: string;
    licenseUrl: string | null;
    sourceUrl: string;
    kind: 'photo' | 'location-map';
}

export type ImageCredits = Record<string, ImageCredit>;

export interface SourceKindLabel {
    kind: 'document' | 'voice' | 'report' | 'archive' | 'ordinary' | 'press';
    label: string;
}

export interface GradeMeaning {
    grade: 'A' | 'B' | 'C' | 'D' | 'E' | 'F';
    meaning: string;
}

export interface LabelledValue {
    label: string;
    value: string;
}

export interface CachiCase {
    date: string;
    title: string;
    summary: string;
    kind: SourceKindLabel;
    detail?: string;
    image?: string;
}

export interface CachiSource {
    title: string;
    publisher: string;
    date: string;
    kind: SourceKindLabel;
    url: string;
}

export interface CachiVideo {
    title: string;
    description: string;
    platform: 'youtube' | 'dailymotion' | 'vimeo';
    youtubeId?: string;
    url: string;
}

export interface TimelineEntry {
    year: string;
    title: string;
    body: string;
}

export interface CachiChapter {
    id: string;
    eyebrow: string;
    title: string;
    body: string[];
    kinds: SourceKindLabel[];
    note?: string;
    image?: string;
    images?: string[];
    concept?: string;
    facts?: { title: string; items: LabelledValue[] };
    stats?: { value: string; label: string }[];
    cases?: CachiCase[];
    document?: { title: string; body: string; url: string; action: string };
    milestones?: TimelineEntry[];
    people?: { name: string; role: string }[];
    timeline?: TimelineEntry[];
    gallery?: { image: string; caption: string }[];
    videos?: CachiVideo[];
    sources?: CachiSource[];
}

export interface CachiDossier {
    kicker: string;
    title: string;
    lead: string;
    quote: string;
    cover: string;
    chapters: CachiChapter[];
    epilogue: string[];
    reference: { name: string };
    summary: CachiSummary;
}

/** Short answers drawn from the dossier, mirrored as FAQPage structured data. */
export interface CachiSummary {
    eyebrow: string;
    title: string;
    items: { question: string; answer: string }[];
}

export interface Coordinates {
    lat: number;
    lng: number;
    approximate: boolean;
    note: string;
}

export interface AtlasCaseSummary {
    slug: string;
    number: number;
    name: string;
    country: string;
    category: string | null;
    seal: GradeMeaning[];
    coordinates: Coordinates | null;
    image: string | null;
}

export interface AtlasSource {
    id: string;
    title: string;
    author: string | null;
    publisher: string | null;
    date: string | null;
    grade: string | null;
    accessed: string | null;
    type: string | null;
    language: string | null;
    places: string[];
    linkStatus: string | null;
    note: string;
    url: string | null;
}

export interface AtlasCandidate {
    name: string;
    facts: LabelledValue[];
    description: string;
    sources: string[];
    reason: string;
    reliability: string;
    decision: string;
    /** Why it is still outside the Atlas: never built, built but needs an on-site check, or existence unverified. */
    status: 'unbuilt' | 'inspection' | 'verification';
    pending: string[];
}

export interface AtlasClaim {
    grade: string;
    kind: string;
    text: string;
    sources: string[];
    seal: GradeMeaning[];
}

export interface AtlasCaseDetail {
    slug: string;
    number: number;
    name: string;
    country: string;
    category: string | null;
    confidence: string | null;
    seal: GradeMeaning[];
    facts: LabelledValue[];
    coordinates: Coordinates | null;
    claims: AtlasClaim[];
    chronology: { date: string; event: string; kind: string; grade: string }[];
    sources: AtlasSource[];
    openQuestions: string[];
    image: { file: string; caption?: string } | null;
}

export interface AtlasNeighbour {
    slug: string;
    name: string;
}

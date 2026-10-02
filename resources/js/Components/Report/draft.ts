import type { PhotoSuggestion } from '@/lib/photos';

export type Step = 1 | 2 | 3 | 4;

export interface LatLng {
    lat: number;
    lng: number;
}

/** Everything the wizard collects; mirrored to localStorage as the draft. */
export interface ReportDraft {
    step: Step;
    type: string | null;
    description: string;
    photos: { id: string; thumb: string }[];
    /** First EXIF suggestion found among the photos (offered, never applied without a tap). */
    suggestion: PhotoSuggestion;
    observedDate: string;
    timeMode: 'range' | 'exact';
    timeRange: string | null;
    exactTime: string;
    dateFromPhoto: boolean;
    point: LatLng | null;
    pointFromPhoto: boolean;
    gaze: string | null;
    nickname: string;
    consent: boolean;
}

export const emptyDraft = (today: string, nickname: string): ReportDraft => ({
    step: 1,
    type: null,
    description: '',
    photos: [],
    suggestion: {},
    observedDate: today,
    timeMode: 'range',
    timeRange: null,
    exactTime: '',
    dateFromPhoto: false,
    point: null,
    pointFromPhoto: false,
    gaze: null,
    nickname,
    consent: false,
});

export const GAZE = ['N', 'NE', 'L', 'SE', 'S', 'SO', 'O', 'NO'] as const;

/** Patch the draft; pass a function when the change depends on the latest state (async work). */
export type DraftUpdate = (patch: Partial<ReportDraft> | ((current: ReportDraft) => Partial<ReportDraft>)) => void;

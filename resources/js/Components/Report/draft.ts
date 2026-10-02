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

/** A report the tower sent back, as the server loads it into the wizard. */
export interface EditingSighting {
    id: number;
    type: string;
    description: string;
    observedDate: string;
    timeRange: string | null;
    exactTime: string | null;
    lat: number;
    lng: number;
    gaze: string | null;
    nickname: string;
    moderationNote: string | null;
    photos: { id: number; thumb: string | null }[];
}

/** Photos that already belong to the report travel in the draft as "kept-<id>". */
const KEPT = 'kept-';
export const isKept = (photoId: string) => photoId.startsWith(KEPT);
export const keptId = (photoId: string) => Number(photoId.slice(KEPT.length));

/** The wizard filled with the report; consent is asked again on resend. */
export const draftFromSighting = (sighting: EditingSighting): ReportDraft => ({
    step: 1,
    type: sighting.type,
    description: sighting.description,
    photos: sighting.photos.map((photo) => ({ id: `${KEPT}${photo.id}`, thumb: photo.thumb ?? '' })),
    suggestion: {},
    observedDate: sighting.observedDate,
    timeMode: sighting.exactTime ? 'exact' : 'range',
    timeRange: sighting.timeRange,
    exactTime: sighting.exactTime ?? '',
    dateFromPhoto: false,
    point: { lat: sighting.lat, lng: sighting.lng },
    pointFromPhoto: false,
    gaze: sighting.gaze,
    nickname: sighting.nickname,
    consent: false,
});

/** Signed thumbnails expire: a restored draft always shows the fresh ones from the server. */
export const withFreshThumbs = (draft: ReportDraft, sighting: EditingSighting): ReportDraft => ({
    ...draft,
    photos: draft.photos.map((photo) => {
        if (!isKept(photo.id)) return photo;
        const fresh = sighting.photos.find((p) => p.id === keptId(photo.id));
        return fresh?.thumb ? { ...photo, thumb: fresh.thumb } : photo;
    }),
});
